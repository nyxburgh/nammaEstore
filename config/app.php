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
define('VENDOR_PREFIX', 'my-vendor');
define('ADMIN_URL',     APP_URL . '/' . ADMIN_PREFIX);
define('VENDOR_URL',    APP_URL . '/' . VENDOR_PREFIX);

// ╔══════════════════════════════════════════════════════════╗
// ║  SUBDOMAIN MODE  (opt-in — uncomment and update)          ║
// ║  Only needed if you want admin/vendor on separate          ║
// ║  subdomains instead of /mc-admin, /my-vendor prefixes.     ║
// ╚══════════════════════════════════════════════════════════╝
// define('ROUTING_MODE', 'subdomain'); // set above instead
// define('ADMIN_URL',    'https://admin.yourdomain.com');
// define('VENDOR_URL',   'https://vendor.yourdomain.com');
// define('ADMIN_HOST',   'admin.yourdomain.com');
// define('VENDOR_HOST',  'vendor.yourdomain.com');

if (!defined('ADMIN_HOST'))  define('ADMIN_HOST',  '');
if (!defined('VENDOR_HOST')) define('VENDOR_HOST', '');

// ── Vendor store URL pattern ──────────────────────────────────
// 'path' → yourdomain.com/shop/techmart-store   (safe, no conflicts)
// 'slug' → yourdomain.com/techmart-store         (clean, root-level slug)
define('VENDOR_STORE_MODE', 'path');   // 'path' | 'slug'
define('VENDOR_STORE_BASE', 'shop');   // used when mode = 'path'

// ── Paths ─────────────────────────────────────────────────────
// WEB_ROOT is defined by the active entry point (index.php or
// public/index.php) — everything web-reachable is resolved from
// THAT, not from BASE_PATH, so uploads/assets always land somewhere
// the browser can actually reach regardless of which entry point
// is serving the request.
define('APP_PATH',    BASE_PATH . '/app');
define('UPLOAD_PATH', WEB_ROOT  . '/uploads');
define('UPLOAD_URL',  APP_URL   . '/uploads');

// Panel asset URLs
define('ADMIN_ASSETS',    APP_URL . '/assets/admin');
define('FRONTEND_ASSETS', APP_URL . '/assets/frontend');
define('VENDOR_ASSETS',   APP_URL . '/assets/vendor-panel');

// ── Panel view paths ──────────────────────────────────────────
define('ADMIN_VIEWS',    APP_PATH . '/admin/Views');
define('FRONTEND_VIEWS', APP_PATH . '/frontend/Views');
define('VENDOR_VIEWS',   APP_PATH . '/vendor-panel/Views');

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
