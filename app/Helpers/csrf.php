<?php
// app/helpers/csrf.php
// Simple CSRF protection helper

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): bool
{
    $token   = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $session = $_SESSION['csrf_token'] ?? '';
    if (!$token || !$session) return false;
    return hash_equals($session, $token);
}

function csrf_check(): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!csrf_verify()) {
            http_response_code(403);
            die('<h1>403 — Invalid CSRF Token</h1><p>Please go back and try again.</p>');
        }
    }
}
