<?php
// app/core/Autoloader.php

class Autoloader
{
    /**
     * Namespace → directory map.
     * All paths are relative to BASE_PATH.
     */
    private static array $map = [
        // ── Shared core & models ──────────────────────────────
        'App\\Core\\'                    => 'app/Core/',
        'App\\Models\\'                  => 'app/Models/',
        'App\\Repositories\\'            => 'app/Repositories/',

        // ── Admin panel ───────────────────────────────────────
        'App\\Admin\\Controllers\\'      => 'admin/Controllers/',
        'App\\Admin\\Services\\'         => 'admin/Services/',

        // ── Customer frontend ─────────────────────────────────
        'App\\Frontend\\Controllers\\'   => 'frontend/Controllers/',
        'App\\Frontend\\Services\\'      => 'frontend/Services/',

        // ── Seller dashboard ────────────────────────────────────
        'App\\SellerPanel\\Controllers\\' => 'seller-panel/Controllers/',
        'App\\SellerPanel\\Services\\'    => 'seller-panel/Services/',

        // ── REST API (future React frontend) ───────────────────
        'App\\Api\\Controllers\\'        => 'api/Controllers/',
    ];

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    private static function load(string $class): void
    {
        foreach (self::$map as $prefix => $dir) {
            if (!str_starts_with($class, $prefix)) continue;

            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file     = BASE_PATH . '/' . $dir . $relative . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}
