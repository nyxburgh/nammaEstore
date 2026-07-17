<?php
use App\Core\Router;
use App\VendorPanel\Controllers\{AuthController, DashboardController, ProductController, OrderController, SettingsController, WalletController, ReturnController, InvoiceController, ShipmentController, NotificationController};

$router = new Router();

$vp = '/' . VENDOR_PREFIX;

// ── Auth ──────────────────────────────────────────────────────
$router->get( $vp,                              AuthController::class . '@loginForm');
$router->get( $vp . '/login',                   AuthController::class . '@loginForm');
$router->post($vp . '/login',                   AuthController::class . '@login');
$router->get( $vp . '/register',                AuthController::class . '@registerForm');
$router->post($vp . '/register',                AuthController::class . '@register');
$router->get( $vp . '/logout',                  AuthController::class . '@logout');
$router->get( $vp . '/verify-email',            AuthController::class . '@verifyEmailForm');
$router->post($vp . '/verify-email',            AuthController::class . '@verifyEmail');
$router->post($vp . '/verify-email/resend',     AuthController::class . '@resendOtp');

// ── Dashboard ─────────────────────────────────────────────────
$router->get( $vp . '/dashboard',               DashboardController::class . '@index');

// ── Products ──────────────────────────────────────────────────
$router->get( $vp . '/products',                ProductController::class . '@index');
$router->get( $vp . '/products/add',            ProductController::class . '@addForm');
$router->post($vp . '/products/add',            ProductController::class . '@store');
$router->get( $vp . '/products/{id}/edit',      ProductController::class . '@editForm');
$router->post($vp . '/products/{id}/edit',      ProductController::class . '@update');
$router->post($vp . '/products/{id}/delete',    ProductController::class . '@delete');
$router->post($vp . '/products/{id}/toggle',    ProductController::class . '@toggle');

// ── Orders ────────────────────────────────────────────────────
$router->get( $vp . '/orders',                  OrderController::class . '@index');
$router->get( $vp . '/orders/{id}',             OrderController::class . '@show');
$router->post($vp . '/orders/{id}/status',      OrderController::class . '@updateStatus');

// ── Settings ──────────────────────────────────────────────────
$router->get( $vp . '/settings',                SettingsController::class . '@index');
$router->post($vp . '/settings',                SettingsController::class . '@update');
$router->post($vp . '/settings/password',       SettingsController::class . '@updatePassword');

// ── Earnings (AJAX data endpoints) ───────────────────────────
$router->get( $vp . '/earnings',                DashboardController::class . '@earnings');
$router->get( $vp . '/commission',              DashboardController::class . '@commission');
$router->get( $vp . '/subscription',            DashboardController::class . '@subscription');
$router->get( $vp . '/reviews',                 DashboardController::class . '@reviews');

// ── Wallet & Payouts ──────────────────────────────────────────
$router->get( $vp . '/wallet',                  WalletController::class . '@index');
$router->post($vp . '/wallet/withdraw',         WalletController::class . '@requestWithdrawal');

// ── Returns ───────────────────────────────────────────────────
$router->get( $vp . '/returns',                 ReturnController::class . '@index');

// ── GST Invoices ──────────────────────────────────────────────
$router->get( $vp . '/invoices',                InvoiceController::class . '@index');
$router->get( $vp . '/invoices/{id}/download',  InvoiceController::class . '@download');

// ── Shipments ─────────────────────────────────────────────────
$router->post($vp . '/shipments/create',        ShipmentController::class . '@create');
$router->post($vp . '/shipments/{id}/track',    ShipmentController::class . '@updateTracking');

// ── Notifications ────────────────────────────────────────────
$router->get( $vp . '/notifications',           NotificationController::class . '@index');
$router->post($vp . '/notifications/{id}/read', NotificationController::class . '@markRead');

return $router;
