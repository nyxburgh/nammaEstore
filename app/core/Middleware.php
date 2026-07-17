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

    // ── Vendor guards ─────────────────────────────────────────
    public static function vendorAuth(): void
    {
        if (!Auth::isVendorLoggedIn()) {
            header('Location: ' . VENDOR_URL . '/login');
            exit;
        }
        // Self-registered vendors must confirm their email OTP before
        // the dashboard opens up. Admin-created vendors are inserted
        // with status='active' directly and never hit this gate.
        $vendorId = Auth::vendorId();
        $profile  = \App\Core\Database::getInstance()->fetchOne(
            "SELECT status FROM `" . DB_PREFIX . "vendor_profiles` WHERE user_id=?", [$vendorId]
        );
        if ($profile && $profile['status'] === 'pending' && !self::isVerifyEmailRoute()) {
            header('Location: ' . VENDOR_URL . '/verify-email');
            exit;
        }
        // A rejected vendor was never actually blocked from the
        // dashboard before this check existed — reject() only flipped
        // the profile status, nothing enforced it on subsequent requests.
        if ($profile && $profile['status'] === 'rejected' && !self::isVerifyEmailRoute()) {
            Auth::logoutVendor();
            header('Location: ' . VENDOR_URL . '/login?rejected=1');
            exit;
        }
    }

    private static function isVerifyEmailRoute(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        return str_contains($uri, '/verify-email') || str_contains($uri, '/logout');
    }

    public static function vendorGuest(): void
    {
        if (Auth::isVendorLoggedIn()) {
            header('Location: ' . VENDOR_URL . '/dashboard');
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

    // ── Shared ────────────────────────────────────────────────
    private static function forbidden(string $viewFile): void
    {
        http_response_code(403);
        if (file_exists($viewFile)) include $viewFile;
        else echo '<h1>403 — Access Denied</h1>';
        exit;
    }
}
