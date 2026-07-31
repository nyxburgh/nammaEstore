<?php
namespace App\Frontend\Services;
use App\Core\{Auth, RateLimiter, Captcha};
use App\Repositories\{UserRepository, CartRepository};

class AuthService
{
    private UserRepository $users;
    public function __construct() { $this->users = new UserRepository(); }

    public function login(string $email, string $password, bool $remember = false, ?string $captchaAnswer = null): array {
        $rlKey = RateLimiter::keyFor('login', $email);

        if (RateLimiter::tooManyAttempts($rlKey, 10, 15)) {
            return ['success' => false, 'message' => 'Too many login attempts. Try again in ' . RateLimiter::availableInMinutes($rlKey) . ' minute(s).'];
        }

        $user = $this->users->findByEmail($email);

        if ($user && !empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $mins = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account temporarily locked. Try again in {$mins} minute(s)."];
        }

        // After 3 failed attempts, require the CAPTCHA to slow down
        // automated guessing without blocking a genuine user outright.
        if ($user && (int) $user['failed_login_attempts'] >= 3) {
            if (!Captcha::verify($captchaAnswer)) {
                RateLimiter::hit($rlKey, 10, 15);
                return ['success' => false, 'message' => 'Incorrect CAPTCHA answer.', 'require_captcha' => true];
            }
        }

        if (!$user || !password_verify($password, $user['password'])) {
            RateLimiter::hit($rlKey, 10, 15);
            if ($user) $this->registerFailedAttempt((int) $user['id'], (int) $user['failed_login_attempts']);
            return ['success' => false, 'message' => 'Invalid email or password.', 'require_captcha' => $user && (int) $user['failed_login_attempts'] >= 2];
        }
        if (!$user['is_active'])          return ['success' => false, 'message' => 'Account has been deactivated.'];
        if ($user['role'] !== 'customer') return ['success' => false, 'message' => 'Please use the seller dashboard to sign in.'];

        // Unverified accounts (signup abandoned before entering the
        // emailed OTP) must verify before the first login completes.
        if (!(int) $user['is_verified']) {
            RateLimiter::clear($rlKey);
            $otp = $this->sendVerificationOtp($user['email']);
            $message = isset($otp['debug_otp'])
                ? "Please verify your email. (Dev mode — no SMTP configured, your verification code is: {$otp['debug_otp']})"
                : 'Please verify your email. We sent a new code to ' . $user['email'] . '.';
            return ['success' => false, 'needs_verification' => true, 'email' => $user['email'], 'message' => $message];
        }

        RateLimiter::clear($rlKey);
        $sessionId = session_id();
        Auth::loginUser($user);
        $update = ['last_login' => date('Y-m-d H:i:s'), 'failed_login_attempts' => 0, 'locked_until' => null];
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $update['remember_token'] = $token;
            setcookie('remember_customer', $user['id'] . ':' . $token, time() + 60 * 60 * 24 * 30, '/', '', HTTPS, true);
        }
        $this->users->update($user['id'], $update);
        (new CartRepository())->mergeGuestCart($sessionId, $user['id']);
        return ['success' => true];
    }

    private function registerFailedAttempt(int $userId, int $currentAttempts): void
    {
        $attempts = $currentAttempts + 1;
        $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 15 * 60) : null;
        $this->users->update($userId, ['failed_login_attempts' => $attempts, 'locked_until' => $lockedUntil]);
    }

    public function register(array $d, ?string $captchaAnswer = null): array {
        if (!Captcha::verify($captchaAnswer)) return ['success' => false, 'message' => 'Incorrect CAPTCHA answer.'];
        if ($this->users->findByEmail($d['email'])) return ['success' => false, 'message' => 'Email already registered.'];
        if (strlen($d['password']) < 8)             return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        $this->users->insert([
            'name'        => $d['name'],
            'email'       => $d['email'],
            'phone'       => $d['phone'] ?? null,
            'password'    => password_hash($d['password'], PASSWORD_DEFAULT),
            'role'        => 'customer',
            'is_active'   => 1,
            'is_verified' => 0,
        ]);
        // The account stays logged-out until the emailed OTP is
        // confirmed — see verifyEmail().
        $otp = $this->sendVerificationOtp($d['email']);
        $result = ['success' => true, 'needs_verification' => true, 'email' => $d['email']];
        if (isset($otp['debug_otp'])) $result['debug_otp'] = $otp['debug_otp'];
        return $result;
    }

    public function sendVerificationOtp(string $email): array {
        return (new \App\Core\Services\OtpService())->send(
            $email, 'customer_email_verify',
            'Verify your email — Namma E Store',
            'Your Namma E Store verification code is'
        );
    }

    /** Confirms the signup OTP, marks the account verified and logs it in. */
    public function verifyEmail(string $email, string $code): array {
        $user = $this->users->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'Account not found. Please register again.'];

        $r = (new \App\Core\Services\OtpService())->verify($email, 'customer_email_verify', $code);
        if (!$r['success']) return $r;

        $this->users->update($user['id'], ['is_verified' => 1, 'email_verified_at' => date('Y-m-d H:i:s')]);
        $sessionId = session_id();
        Auth::loginUser($user);
        (new CartRepository())->mergeGuestCart($sessionId, $user['id']);
        return ['success' => true];
    }

    /** Emails a password-reset OTP. Silent when the email is unknown, so
     *  the form can't be used to probe which addresses are registered. */
    public function sendPasswordResetOtp(string $email): void {
        if (!$this->users->findByEmail($email)) return;
        (new \App\Core\Services\OtpService())->send(
            $email, 'password_reset',
            'Reset your password — Namma E Store',
            'Your Namma E Store password reset code is'
        );
    }

    public function resetPassword(string $email, string $code, string $newPassword): array {
        if (strlen($newPassword) < 8) return ['success' => false, 'message' => 'Password must be at least 8 characters.'];

        $r = (new \App\Core\Services\OtpService())->verify($email, 'password_reset', $code);
        if (!$r['success']) return $r;

        $user = $this->users->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'Account not found.'];

        $this->users->update($user['id'], [
            'password'              => password_hash($newPassword, PASSWORD_DEFAULT),
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'remember_token'        => null,
        ]);
        return ['success' => true];
    }

    public function logout(): void {
        setcookie('remember_customer', '', time() - 3600, '/');
        Auth::logoutUser();
    }
}
