<?php
/**
 * Namma E Store — Root Entry Point
 *
 * Use this when the whole project folder is the web root, e.g.
 *   XAMPP:  htdocs/mycart/          → http://localhost/mycart/
 *   cPanel: public_html/            → https://yourdomain.com/
 *
 * WEB_ROOT is the directory actually reachable over HTTP — assets,
 * uploads, and generated URLs are all resolved relative to it, so the
 * exact same app/ code works whether this file or public/index.php
 * is the active entry point. See public/index.php for the alternative
 * (app/config kept outside the web root, for VPS/Nginx deployments).
 */

define('BASE_PATH', __DIR__);
define('WEB_ROOT', __DIR__);

require BASE_PATH . '/app/bootstrap.php';
