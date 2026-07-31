<?php
// config/database.php
//
// Reads from environment variables first, falling back to XAMPP's
// out-of-the-box defaults (root / no password) when they're not set.
// This means:
//   • XAMPP local: works with zero configuration, no edits needed.
//   • Live server: set DB_HOST/DB_NAME/DB_USER/DB_PASS as real
//     environment variables (via .htaccess SetEnv, cPanel's "Setup
//     Node.js/PHP App" env panel, or your host's env config) and
//     this file picks them up automatically — no code change,
//     no risk of accidentally deploying local credentials.

return [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'dbname'   => getenv('DB_NAME') ?: 'mycart_marketplace',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
