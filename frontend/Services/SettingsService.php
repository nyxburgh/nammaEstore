<?php
namespace App\Frontend\Services;
use App\Core\Database;

class SettingsService
{
    private static array $cache = [];

    public static function get(string $key, string $default = ''): string {
        if (empty(self::$cache)) {
            $rows = Database::getInstance()->fetchAll("SELECT `key`,`value` FROM `".DB_PREFIX."settings`");
            foreach ($rows as $r) self::$cache[$r['key']] = $r['value'];
        }
        return self::$cache[$key] ?? $default;
    }

    public static function all(): array {
        self::get('site_name');
        return self::$cache;
    }
}
