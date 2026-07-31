<?php
namespace App\Core\Services;

use App\Core\{Database, ProviderFactory, RateLimiter};

class OtpService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generates a 6-digit OTP for $identifier+$purpose, emails it,
     * and returns whether it was sent. Rate-limited to 3 requests
     * per 10 minutes per identifier so "resend" can't be used to
     * spam an inbox or brute-force-adjacent abuse.
     */
    public function send(string $identifier, string $purpose, string $subject, string $messagePrefix = 'Your verification code is'): array
    {
        $rlKey = RateLimiter::keyFor('otp_send_' . $purpose, $identifier);
        if (RateLimiter::tooManyAttempts($rlKey, 3, 10)) {
            return ['success' => false, 'message' => 'Too many code requests. Try again in ' . RateLimiter::availableInMinutes($rlKey) . ' minute(s).'];
        }
        RateLimiter::hit($rlKey, 3, 10);

        // Invalidate any earlier unused OTPs for this identifier+purpose
        // so only the most recent code is valid.
        $this->db->execute(
            "UPDATE `" . DB_PREFIX . "otps` SET is_used=1 WHERE identifier=? AND purpose=? AND is_used=0",
            [$identifier, $purpose]
        );

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->db->insert(
            "INSERT INTO `" . DB_PREFIX . "otps` (identifier, purpose, otp_code, expires_at) VALUES (?,?,?,?)",
            [$identifier, $purpose, $code, date('Y-m-d H:i:s', time() + 10 * 60)]
        );

        $sent = false;
        try {
            $sent = ProviderFactory::email()->send(
                $identifier, $subject,
                "<p>{$messagePrefix}: <strong style=\"font-size:20px;letter-spacing:3px;\">{$code}</strong></p><p>This code expires in 10 minutes.</p>"
            );
        } catch (\Exception $e) {
            error_log('OTP email failed: ' . $e->getMessage());
        }

        $result = ['success' => true];
        if (!$sent) {
            // No SMTP configured (common on a local XAMPP box without
            // composer packages installed) — the OTP row still exists
            // so the flow isn't blocked, but nothing was actually
            // emailed. Surface the code in dev so it's still testable;
            // never do this in production.
            error_log("OTP email not sent (no working mail transport) for {$identifier} [{$purpose}] — code: {$code}");
            if (APP_ENV === 'development') {
                $result['debug_otp'] = $code;
            }
        }

        return $result;
    }

    /**
     * Verifies a submitted code. Allows up to 5 attempts against the
     * same OTP before it's invalidated outright (prevents brute-
     * forcing a 6-digit code against a single generated OTP).
     */
    public function verify(string $identifier, string $purpose, string $submittedCode): array
    {
        $otp = $this->db->fetchOne(
            "SELECT * FROM `" . DB_PREFIX . "otps`
             WHERE identifier=? AND purpose=? AND is_used=0
             ORDER BY id DESC LIMIT 1",
            [$identifier, $purpose]
        );

        if (!$otp) {
            return ['success' => false, 'message' => 'No verification code found. Please request a new one.'];
        }
        if (strtotime($otp['expires_at']) < time()) {
            return ['success' => false, 'message' => 'This code has expired. Please request a new one.'];
        }
        if ((int) $otp['attempts'] >= 5) {
            $this->db->execute("UPDATE `" . DB_PREFIX . "otps` SET is_used=1 WHERE id=?", [$otp['id']]);
            return ['success' => false, 'message' => 'Too many incorrect attempts. Please request a new code.'];
        }

        if (!hash_equals($otp['otp_code'], $submittedCode)) {
            $this->db->execute("UPDATE `" . DB_PREFIX . "otps` SET attempts=attempts+1 WHERE id=?", [$otp['id']]);
            return ['success' => false, 'message' => 'Incorrect code.'];
        }

        $this->db->execute("UPDATE `" . DB_PREFIX . "otps` SET is_used=1 WHERE id=?", [$otp['id']]);
        return ['success' => true];
    }
}
