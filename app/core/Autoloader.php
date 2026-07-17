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
        'App\\Core\\'                    => 'app/core/',
        'App\\Models\\'                  => 'app/models/',
        'App\\Repositories\\'            => 'app/repositories/',

        // ── Admin panel ───────────────────────────────────────
        'App\\Admin\\Controllers\\'      => 'app/admin/Controllers/',
        'App\\Admin\\Services\\'         => 'app/admin/Services/',

        // ── Customer frontend ─────────────────────────────────
        'App\\Frontend\\Controllers\\'   => 'app/frontend/Controllers/',
        'App\\Frontend\\Services\\'      => 'app/frontend/Services/',

        // ── Vendor dashboard ──────────────────────────────────
        'App\\VendorPanel\\Controllers\\' => 'app/vendor-panel/Controllers/',
        'App\\VendorPanel\\Services\\'    => 'app/vendor-panel/Services/',
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
