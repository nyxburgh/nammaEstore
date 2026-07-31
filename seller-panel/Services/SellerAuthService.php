<?php
namespace App\SellerPanel\Services;
use App\Core\{Auth, Database, RateLimiter};
use App\Repositories\UserRepository;

class SellerAuthService
{
    private UserRepository $users;
    public function __construct() { $this->users = new UserRepository(); }

    public function login(string $email, string $password): array {
        $rlKey = RateLimiter::keyFor('seller_login', $email);
        if (RateLimiter::tooManyAttempts($rlKey, 10, 15)) {
            return ['success' => false, 'message' => 'Too many login attempts. Try again in ' . RateLimiter::availableInMinutes($rlKey) . ' minute(s).'];
        }

        $user = $this->users->findByEmail($email);

        if ($user && !empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $mins = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account temporarily locked. Try again in {$mins} minute(s)."];
        }

        if (!$user || !password_verify($password, $user['password'])) {
            RateLimiter::hit($rlKey, 10, 15);
            if ($user) {
                $attempts = (int) $user['failed_login_attempts'] + 1;
                $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 15 * 60) : null;
                $this->users->update($user['id'], ['failed_login_attempts' => $attempts, 'locked_until' => $lockedUntil]);
            }
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if (!$user['is_active'])
            return ['success' => false, 'message' => 'Your account is deactivated. Contact support.'];
        if (!in_array($user['role'], ['seller', 'customer']))
            return ['success' => false, 'message' => 'Access denied.'];
        // Auto-upgrade customer to seller if no seller profile yet
        $db = Database::getInstance();
        $vp = $db->fetchOne("SELECT * FROM `".DB_PREFIX."seller_profiles` WHERE user_id=?", [$user['id']]);
        if (!$vp) return ['success' => false, 'message' => 'No seller profile found. Please register as a seller.'];

        RateLimiter::clear($rlKey);
        Auth::loginSeller($user);
        $this->users->update($user['id'], ['last_login' => date('Y-m-d H:i:s'), 'failed_login_attempts' => 0, 'locked_until' => null]);
        return ['success' => true];
    }

    public function register(array $d): array {
        if ($this->users->findByEmail($d['email']))
            return ['success' => false, 'message' => 'Email already registered.'];
        if (strlen($d['password']) < 8)
            return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        if (empty(trim($d['shop_name'])))
            return ['success' => false, 'message' => 'Shop name is required.'];
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $uid = $this->users->insert([
                'name'      => $d['name'],
                'email'     => $d['email'],
                'phone'     => $d['phone'] ?? null,
                'password'  => password_hash($d['password'], PASSWORD_DEFAULT),
                'role'      => 'seller',
                'is_active' => 1,
            ]);
            $shopSlug = \slugify($d['shop_name'] . '-' . $uid);
            // Self-registered sellers start pending/unverified — the
            // shop does NOT go live until the emailed OTP is confirmed.
            // Admin-created sellers (SellerService::create in the admin
            // panel) are unaffected by this and remain immediately active.
            $db->insert(
                "INSERT INTO `".DB_PREFIX."seller_profiles` (user_id,shop_name,shop_slug,description,status) VALUES (?,?,?,?,'pending')",
                [$uid, $d['shop_name'], $shopSlug, $d['description'] ?? null]
            );
            // Assign free plan
            $plan = $db->fetchOne("SELECT id FROM `".DB_PREFIX."subscription_plans` WHERE slug='free' LIMIT 1");
            if ($plan) {
                $db->insert("INSERT INTO `".DB_PREFIX."seller_subscriptions` (seller_id,plan_id,status,started_at) VALUES (?,?,'active',NOW())", [$uid,$plan['id']]);
            }
            $db->commit();
            $user = $this->users->findById($uid);
            Auth::loginSeller($user);

            $otp = (new \App\Core\Services\OtpService())->send(
                $d['email'], 'seller_email_verify',
                'Verify your email — ' . $d['shop_name'],
                'Your Namma E Store seller verification code is'
            );

            $result = ['success' => true, 'needs_verification' => true];
            if (isset($otp['debug_otp'])) $result['debug_otp'] = $otp['debug_otp'];
            return $result;
        } catch (\Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function verifyEmail(int $sellerId, string $email, string $code): array
    {
        $r = (new \App\Core\Services\OtpService())->verify($email, 'seller_email_verify', $code);
        if (!$r['success']) return $r;

        $db = Database::getInstance();
        $db->execute(
            "UPDATE `".DB_PREFIX."seller_profiles` SET status='active', approved_at=NOW() WHERE user_id=?",
            [$sellerId]
        );
        $db->execute(
            "UPDATE `".DB_PREFIX."users` SET is_verified=1, email_verified_at=NOW() WHERE id=?",
            [$sellerId]
        );
        return ['success' => true];
    }

    public function resendVerification(int $sellerId, string $email, string $shopName): array
    {
        return (new \App\Core\Services\OtpService())->send(
            $email, 'seller_email_verify',
            'Verify your email — ' . $shopName,
            'Your Namma E Store seller verification code is'
        );
    }

    public function logout(): void { Auth::logoutSeller(); }
}
