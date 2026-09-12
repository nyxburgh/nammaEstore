<?php
// app/core/Middleware.php
namespace App\Core;

class Middleware
{
    // ── Admin guards ──────────────────────────────────────────
    public static function adminAuth(): void
    {
        if (!Auth::isAdminLoggedIn()) {
            header('Location: ' . ADMIN_URL . '/login');
            exit;
        }
    }

    public static function superAdmin(): void
    {
        self::adminAuth();
        if (!Auth::isSuperAdmin()) {
            self::forbidden(ADMIN_VIEWS . '/errors/403.php');
        }
    }

    public static function can(string $module, string $action = 'view'): void
    {
        self::adminAuth();
        if (!Auth::can($module, $action)) {
            self::forbidden(ADMIN_VIEWS . '/errors/403.php');
        }
    }

    public static function adminGuest(): void
    {
        if (Auth::isAdminLoggedIn()) {
            header('Location: ' . ADMIN_URL . '/dashboard');
            exit;
        }
    }

    // ── Seller guards ─────────────────────────────────────────
    public static function sellerAuth(): void
    {
        if (!Auth::isSellerLoggedIn()) {
            header('Location: ' . SELLER_URL . '/login');
            exit;
        }
        // Self-registered sellers must confirm their email OTP before
        // the dashboard opens up. Admin-created sellers are inserted
        // with status='active' directly and never hit this gate.
        $sellerId = Auth::sellerId();
        $profile  = \App\Core\Database::getInstance()->fetchOne(
            "SELECT status FROM `" . DB_PREFIX . "seller_profiles` WHERE user_id=?", [$sellerId]
        );
        if ($profile && $profile['status'] === 'pending' && !self::isVerifyEmailRoute()) {
            header('Location: ' . SELLER_URL . '/verify-email');
            exit;
        }
        // A rejected seller was never actually blocked from the
        // dashboard before this check existed — reject() only flipped
        // the profile status, nothing enforced it on subsequent requests.
        if ($profile && $profile['status'] === 'rejected' && !self::isVerifyEmailRoute()) {
            Auth::logoutSeller();
            header('Location: ' . SELLER_URL . '/login?rejected=1');
            exit;
        }
    }

    private static function isVerifyEmailRoute(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        return str_contains($uri, '/verify-email') || str_contains($uri, '/logout');
    }

    public static function sellerGuest(): void
    {
        if (Auth::isSellerLoggedIn()) {
            header('Location: ' . SELLER_URL . '/dashboard');
            exit;
        }
    }

    // ── Customer guards ───────────────────────────────────────
    public static function userAuth(): void
    {
        if (!Auth::isUserLoggedIn()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    public static function userGuest(): void
    {
        if (Auth::isUserLoggedIn()) {
            header('Location: ' . APP_URL . '/account');
            exit;
        }
    }

    // ── API guard (JWT bearer token, not the session cookie) ───
    /**
     * Verifies the Authorization: Bearer <token> header and returns
     * its claims (sub/role/email/...). Emits a 401 JSON response and
     * exits on failure — callers can assume they get valid claims back
     * or the request already ended.
     */
    public static function apiAuth(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($header === '' && function_exists('apache_request_headers')) {
            $header = apache_request_headers()['Authorization'] ?? '';
        }
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            self::jsonError('Missing or malformed Authorization header.');
        }
        $claims = \App\Core\Services\JwtService::decode($m[1]);
        if (!$claims) {
            self::jsonError('Invalid or expired token.');
        }
        return $claims;
    }

    private static function jsonError(string $message): never
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    // ── Shared ────────────────────────────────────────────────
    private static function forbidden(string $viewFile): void
    {
        http_response_code(403);
        if (file_exists($viewFile)) include $viewFile;
        else echo '<h1>403 — Access Denied</h1>';
        exit;
    }
}
