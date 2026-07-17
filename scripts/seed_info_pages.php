<?php
/**
 * One-off script: renders the static Info Center content views into
 * HTML and seeds them into mc_pages (migration 010) so admins can
 * edit them from the panel. Live URLs are converted into tokens
 * ({{app_url}}, {{vendor_url}}, {{site_name}}) that the frontend
 * re-expands at render time.
 *
 * Run once:  php scripts/seed_info_pages.php
 * Safe to re-run — existing slugs are skipped.
 */

define('BASE_PATH', dirname(__DIR__));
define('WEB_ROOT', BASE_PATH);                       // CLI: no entry point defines it
define('APP_URL', 'http://localhost/mycart');        // CLI: no HTTP context to auto-detect
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/app/core/Autoloader.php';
Autoloader::register();
require BASE_PATH . '/app/helpers/functions.php';

use App\Core\Database;
use App\Frontend\Controllers\PageController;
use App\Frontend\Services\SettingsService;

$db       = Database::getInstance();
$siteName = SettingsService::get('site_name', 'Namma E Store');

$seeded = 0;
foreach (PageController::PAGES as $slug => [$icon, $title, $short]) {
    $exists = $db->fetchOne('SELECT id FROM `' . DB_PREFIX . 'pages` WHERE slug=?', [$slug]);
    if ($exists) {
        echo "Skip (exists): $slug\n";
        continue;
    }

    $file = FRONTEND_VIEWS . '/pages/content/' . $slug . '.php';
    $html = '';
    if (is_file($file)) {
        ob_start();
        include $file;
        $html = trim(ob_get_clean());
        // Tokenize live values so the content survives URL / name changes.
        // VENDOR_URL first — it contains APP_URL as a prefix.
        $html = str_replace(
            [VENDOR_URL, APP_URL, $siteName],
            ['{{vendor_url}}', '{{app_url}}', '{{site_name}}'],
            $html
        );
    }

    $db->insert(
        'INSERT INTO `' . DB_PREFIX . 'pages` (slug, icon, title, short_description, content, is_active, sort_order)
         VALUES (?, ?, ?, ?, ?, 1, ?)',
        [$slug, $icon, $title, $short, $html, ($seeded + 1) * 10]
    );
    echo "Seeded: $slug (" . strlen($html) . " bytes)\n";
    $seeded++;
}
echo "\nDone. $seeded page(s) seeded.\n";
