<?php
namespace App\Frontend\Services;

use App\Frontend\Controllers\PageController;
use App\Repositories\PageRepository;

/**
 * Read side of the Info Center pages. Pages live in mc_pages (admin-
 * editable); the PageController::PAGES constant + content view files
 * remain as a fallback so the frontend keeps working before migration
 * 010 has been run.
 */
class PageService
{
    /** @var array<string, array{0:string,1:string,2:string}>|null slug => [icon, title, short] */
    private static ?array $listCache = null;

    /** All active pages: slug => [icon, title, short]. */
    public static function all(): array
    {
        if (self::$listCache !== null) return self::$listCache;
        try {
            $rows = (new PageRepository())->allActive();
            if ($rows) {
                $out = [];
                foreach ($rows as $r) {
                    $out[$r['slug']] = [$r['icon'], $r['title'], $r['short_description'] ?? ''];
                }
                return self::$listCache = $out;
            }
        } catch (\Throwable) {
            // table missing (migration not run) — fall through
        }
        return self::$listCache = PageController::PAGES;
    }

    /** Full row for one page, or null. DB first, file fallback. */
    public static function find(string $slug): ?array
    {
        try {
            $row = (new PageRepository())->findBySlug($slug);
            if ($row) return $row['is_active'] ? $row : null;
        } catch (\Throwable) {
            // table missing — fall through to file fallback
        }
        if (isset(PageController::PAGES[$slug])) {
            [$icon, $title, $short] = PageController::PAGES[$slug];
            return [
                'slug' => $slug, 'icon' => $icon, 'title' => $title,
                'short_description' => $short, 'content' => null, 'is_active' => 1,
            ];
        }
        return null;
    }

    /** Replace content tokens with live values. */
    public static function render(string $html): string
    {
        return strtr($html, [
            '{{app_url}}'    => APP_URL,
            '{{seller_url}}' => SELLER_URL,
            '{{site_name}}'  => e(SettingsService::get('site_name', 'Namma E Store')),
            '{{site_email}}' => e(SettingsService::get('site_email', 'info@nammaestore.com')),
            '{{site_phone}}' => e(SettingsService::get('site_phone', '+91 9999999999')),
        ]);
    }
}
