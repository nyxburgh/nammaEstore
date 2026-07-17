<?php
// app/core/Auth.php
namespace App\Core;

class Auth
{
    // ── Admin ─────────────────────────────────────────────────
    public static function loginAdmin(array $admin): void
    {
        $_SESSION['admin'] = [
            'id'    => $admin['id'],
            'name'  => $admin['name'],
            'email' => $admin['email'],
            'role'  => $admin['role'],
        ];
    }
    public static function admin(): ?array        { return $_SESSION['admin'] ?? null; }
    public static function isAdminLoggedIn(): bool { return isset($_SESSION['admin']); }
    public static function isSuperAdmin(): bool    { return ($_SESSION['admin']['role'] ?? '') === 'super_admin'; }
    public static function isSubAdmin(): bool      { return ($_SESSION['admin']['role'] ?? '') === 'sub_admin'; }
    public static function adminId(): ?int         { return isset($_SESSION['admin']) ? (int) $_SESSION['admin']['id'] : null; }
    public static function logoutAdmin(): void     { unset($_SESSION['admin'], $_SESSION['admin_permissions']); }

    public static function loadPermissions(array $perms): void
    {
        $_SESSION['admin_permissions'] = $perms;
    }
    public static function can(string $module, string $action = 'view'): bool
    {
        if (self::isSuperAdmin()) return true;
        $perms = $_SESSION['admin_permissions'] ?? [];
        return in_array("{$module}.{$action}", $perms, true)
            || in_array("{$module}.*", $perms, true);
    }

    // ── Vendor ────────────────────────────────────────────────
    public static function loginVendor(array $user): void
    {
        $_SESSION['vendor'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? null,
            'role'  => $user['role'] ?? 'vendor',
        ];
    }
    public static function vendor(): ?array         { return $_SESSION['vendor'] ?? null; }
    public static function isVendorLoggedIn(): bool  { return isset($_SESSION['vendor']); }
    public static function vendorId(): ?int          { return isset($_SESSION['vendor']) ? (int) $_SESSION['vendor']['id'] : null; }
    public static function logoutVendor(): void      { unset($_SESSION['vendor']); }

    // ── Customer ──────────────────────────────────────────────
    public static function loginUser(array $user): void
    {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? null,
            'role'  => $user['role'],
        ];
    }
    public static function user(): ?array          { return $_SESSION['user'] ?? null; }
    public static function isUserLoggedIn(): bool  { return isset($_SESSION['user']); }
    public static function userId(): ?int          { return isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null; }
    public static function logoutUser(): void      { unset($_SESSION['user']); }
}
