<?php
// app/bootstrap.php
//
// NOTE: This file was referenced by both index.php and public/index.php
// but was not present in the delivered v1 build — the app could not
// boot at all. Reconstructed here from config/app.php's routing-mode
// contract and the three routes.php files.

// ── Environment file (.env) ──────────────────────────────────
// Optional. If present at the project root, its KEY=VALUE lines are
// loaded via putenv() before config/*.php runs, so getenv() sees
// them consistently across Apache mod_php, PHP-FPM, and CLI alike
// (Apache's SetEnv doesn't reliably reach PHP-FPM, so this is the
// dependable cross-environment mechanism).
//
// On XAMPP: just don't create a .env file — every config falls back
// to its documented local default automatically.
// On a live server: copy .env.example to .env and fill in real
// values. NEVER commit .env to version control.
$__envFile = BASE_PATH . '/.env';
if (is_file($__envFile)) {
    foreach (file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        $__line = trim($__line);
        if ($__line === '' || str_starts_with($__line, '#') || !str_contains($__line, '=')) {
            continue;
        }
        [$__key, $__val] = array_map('trim', explode('=', $__line, 2));
        $__val = trim($__val, "\"'");
        if (getenv($__key) === false) {
            putenv("{$__key}={$__val}");
            $_ENV[$__key] = $__val;
        }
    }
    unset($__line, $__key, $__val);
}
unset($__envFile);

// ── Config ───────────────────────────────────────────────────
require BASE_PATH . '/config/app.php';

// ── Composer autoloader (dompdf, PHPMailer, etc.) ────────────
// Optional — the app degrades gracefully (see SmtpEmailProvider,
// InvoiceService) if `composer install` hasn't been run yet. Run
// it from the project root to enable PDF invoices and richer SMTP.
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// ── Autoloading (this app's own classes) ─────────────────────
require BASE_PATH . '/app/core/Autoloader.php';
Autoloader::register();

// ── Helpers ──────────────────────────────────────────────────
require BASE_PATH . '/app/helpers/functions.php';
require BASE_PATH . '/app/helpers/csrf.php';
require BASE_PATH . '/app/helpers/config.php';

// ── Session ──────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => HTTPS,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Security headers ──────────────────────────────────────────
// Applied to every response. CSP allows 'unsafe-inline' for both
// style and script because the admin/vendor panels still rely
// heavily on inline onclick= handlers and <style> blocks by design
// (frontend was cleaned up to external CSS/JS in Phase 6; admin/
// vendor panels were explicitly left as-is). Once those panels are
// eventually externalized too, tighten script-src to drop
// 'unsafe-inline' and rely on the external <script src> files only.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; frame-ancestors 'self';");
if (HTTPS) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// ── Panel resolution ─────────────────────────────────────────
// Works out which of the three routers (admin / vendor / frontend)
// should handle this request, based on ROUTING_MODE.
use App\Core\Router;

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Strip the sub-path the app is installed under (e.g. /mycart or /mycart/public)
// so route patterns defined as '/' , '/mc-admin', etc. match correctly
// regardless of install depth.
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = '/' . ltrim($uri, '/');

$panel = 'frontend';

if (ROUTING_MODE === 'subdomain') {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (ADMIN_HOST && $host === ADMIN_HOST)   $panel = 'admin';
    if (VENDOR_HOST && $host === VENDOR_HOST) $panel = 'vendor';
} else {
    if (str_starts_with($uri, '/' . ADMIN_PREFIX))  $panel = 'admin';
    if (str_starts_with($uri, '/' . VENDOR_PREFIX)) $panel = 'vendor';
}

$routesFile = match ($panel) {
    'admin'  => BASE_PATH . '/app/admin/routes.php',
    'vendor' => BASE_PATH . '/app/vendor-panel/routes.php',
    default  => BASE_PATH . '/app/frontend/routes.php',
};

/** @var Router $router */
require $routesFile;
$router->dispatch($uri, $method);
