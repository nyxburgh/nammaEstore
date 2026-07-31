<?php
// config/app.php
//
// ── Environment auto-detection ──────────────────────────────────
// URL, scheme, and asset/upload paths are all derived from the
// actual HTTP request and from WEB_ROOT (defined by whichever entry
// point — index.php or public/index.php — is currently running).
// This means the SAME config works unmodified on:
//   • XAMPP local:  http://localhost/mycart/
//   • Live server:  https://yourdomain.com/   (root or public/ webroot)
// Nothing here needs manual editing when moving environments — only
// APP_ENV below should be flipped for production (disables verbose
// error display).

define('APP_NAME',    'Namma E Store');
define('APP_VERSION', '1.0.0');

// 'development' shows PHP errors on screen — set APP_ENV=production
// in your .env file before going live (see .env.example).
define('APP_ENV', getenv('APP_ENV') ?: 'development'); // development | production

// ── Routing mode ─────────────────────────────────────────────
//  'prefix'    → http://localhost/mycart/mc-admin/   (default — works everywhere)
//  'subdomain' → http://admin.mycart.com/             (opt-in, needs DNS + vhosts)
define('ROUTING_MODE', 'prefix');

// ── HTTPS detection ──────────────────────────────────────────
// Auto-detects real scheme, including behind a reverse proxy/load
// balancer that terminates TLS and forwards plain HTTP (common on
// live hosting) via X-Forwarded-Proto.
$__https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
define('HTTPS', $__https);

// ── Base URL detection ───────────────────────────────────────
// Derives scheme://host + the sub-path the app is installed under,
// straight from the request — so http://localhost/mycart and
// https://yourdomain.com (installed at the domain root, or in a
// sub-folder) all resolve correctly without editing this file.
//
// Override only if auto-detection is wrong for your setup (e.g. CLI
// scripts/cron with no $_SERVER context) by uncommenting the line
// below and hardcoding it.
// define('APP_URL', 'https://yourdomain.com');

if (!defined('APP_URL')) {
    $scheme = HTTPS ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Sub-path the app is installed under, e.g. "/mycart" on XAMPP,
    // or "" when public/ (or the project root) IS the web root.
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    define('APP_URL', $scheme . '://' . $host . $basePath);
}

define('ADMIN_PREFIX',  'mc-admin');
define('SELLER_PREFIX', 'my-seller');
define('ADMIN_URL',     APP_URL . '/' . ADMIN_PREFIX);
define('SELLER_URL',    APP_URL . '/' . SELLER_PREFIX);

// ╔══════════════════════════════════════════════════════════╗
// ║  SUBDOMAIN MODE  (opt-in — uncomment and update)          ║
// ║  Only needed if you want admin/seller on separate          ║
// ║  subdomains instead of /mc-admin, /my-seller prefixes.     ║
// ╚══════════════════════════════════════════════════════════╝
// define('ROUTING_MODE', 'subdomain'); // set above instead
// define('ADMIN_URL',    'https://admin.yourdomain.com');
// define('SELLER_URL',   'https://seller.yourdomain.com');
// define('ADMIN_HOST',   'admin.yourdomain.com');
// define('SELLER_HOST',  'seller.yourdomain.com');

if (!defined('ADMIN_HOST'))  define('ADMIN_HOST',  '');
if (!defined('SELLER_HOST')) define('SELLER_HOST', '');

// ── Seller store URL pattern ──────────────────────────────────
// 'path' → yourdomain.com/shop/techmart-store   (safe, no conflicts)
// 'slug' → yourdomain.com/techmart-store         (clean, root-level slug)
define('SELLER_STORE_MODE', 'path');   // 'path' | 'slug'
define('SELLER_STORE_BASE', 'shop');   // used when mode = 'path'

// ── Paths ─────────────────────────────────────────────────────
define('APP_PATH', BASE_PATH . '/app');

// Uploaded files always physically live in public/uploads/ — regardless
// of which entry point served this request. APP_URL, by contrast, DOES
// vary by entry point (it's derived from SCRIPT_NAME): it already
// includes "/public" when public/index.php served the request, but
// doesn't when the deprecated root index.php did. So UPLOAD_URL adds
// "/public" itself only when APP_URL hasn't already got it — otherwise
// uploaded images would 404 under the root entry point even though the
// files exist right there in public/uploads/.
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');
define('UPLOAD_URL',  APP_URL . (str_ends_with(APP_URL, '/public') ? '' : '/public') . '/uploads');

// Panel asset URLs
define('ADMIN_ASSETS',    APP_URL . '/assets/admin');
define('FRONTEND_ASSETS', APP_URL . '/assets/frontend');
define('SELLER_ASSETS',   APP_URL . '/assets/seller-panel');

// ── Panel view paths ──────────────────────────────────────────
// Panels (admin/, frontend/, seller-panel/, api/) live as siblings of
// app/ at the project root, not under it — app/ now holds only the
// shared library (Core, Config, Helpers, Models, Repositories).
define('ADMIN_VIEWS',    BASE_PATH . '/admin/Views');
define('FRONTEND_VIEWS', BASE_PATH . '/frontend/Views');
define('SELLER_VIEWS',   BASE_PATH . '/seller-panel/Views');

// ── Database ──────────────────────────────────────────────────
define('DB_PREFIX', 'mc_');

// ── Session ───────────────────────────────────────────────────
define('SESSION_NAME',     'mycart_session');
define('SESSION_LIFETIME', 7200);

// ── Pagination ────────────────────────────────────────────────
define('ITEMS_PER_PAGE', 15);

// ── Timezone ──────────────────────────────────────────────────
date_default_timezone_set('Asia/Kolkata');

// ── Error Reporting ───────────────────────────────────────────
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
