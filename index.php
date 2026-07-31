<?php
/**
 * Namma E Store — Root Entry Point (DEPRECATED)
 *
 * This project now standardizes on public/index.php as the only
 * entry point — set your web server's document root to public/
 * (on XAMPP, just browse to http://localhost/nammaestore/public/
 * instead of the project root; no vhost change required). That
 * keeps app/, admin/, frontend/, seller-panel/, api/ outside the
 * web-servable tree entirely.
 *
 * This file is kept only as a fallback for hosts that truly can't
 * point their doc root anywhere but the project folder (e.g. some
 * shared cPanel setups). Static assets moved to public/assets/, so
 * CSS/JS will 404 under this entry point — use public/index.php.
 */

define('BASE_PATH', __DIR__);
define('WEB_ROOT', __DIR__);

require BASE_PATH . '/app/bootstrap.php';
