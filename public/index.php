<?php
/**
 * Namma E Store — public/ Entry Point
 *
 * Use this when the web server's document root points at public/
 * directly (typical VPS/Nginx setup), keeping app/, config/, and
 * database.sql outside the web-servable tree entirely — the more
 * secure option for a live server.
 *
 * WEB_ROOT is this directory. BASE_PATH is one level up, where
 * app/bootstrap.php actually lives.
 */

define('BASE_PATH', dirname(__DIR__));
define('WEB_ROOT', __DIR__);

require BASE_PATH . '/app/bootstrap.php'; 