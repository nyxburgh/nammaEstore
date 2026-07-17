<?php
// app/admin/routes.php

use App\Core\Router;
use App\Admin\Controllers\AuthController;
use App\Admin\Controllers\DashboardController;
use App\Admin\Controllers\VendorController;
use App\Admin\Controllers\CustomerController;
use App\Admin\Controllers\ProductController;
use App\Admin\Controllers\OrderController;
use App\Admin\Controllers\SubAdminController;
use App\Admin\Controllers\BannerController;
use App\Admin\Controllers\ActivityController;
use App\Admin\Controllers\SettingsController;
use App\Admin\Controllers\ReportController;
use App\Admin\Controllers\ReviewController;
use App\Admin\Controllers\CategoryController;
use App\Admin\Controllers\PaymentController;
use App\Admin\Controllers\SettlementController;
use App\Admin\Controllers\ReturnController;
use App\Admin\Controllers\DisputeController;
use App\Admin\Controllers\CouponController;
use App\Admin\Controllers\GiftCardController;
use App\Admin\Controllers\BrandController;
use App\Admin\Controllers\InvoiceController;
use App\Admin\Controllers\ShipmentController;
use App\Admin\Controllers\PageController;

$router = new Router();
$p = '/' . ADMIN_PREFIX; // /mc-admin

// ── Root redirect ─────────────────────────────────────────────
$router->get($p, AuthController::class . '@index');

// ── Auth ──────────────────────────────────────────────────────
$router->get("$p/login",  AuthController::class . '@loginForm');
$router->post("$p/login", AuthController::class . '@login');
$router->get("$p/logout", AuthController::class . '@logout');

// ── Dashboard ─────────────────────────────────────────────────
$router->get("$p/dashboard", DashboardController::class . '@index');

// ── Vendors ───────────────────────────────────────────────────
$router->get("$p/vendors",                 VendorController::class . '@index');
$router->get("$p/vendors/create",          VendorController::class . '@create');
$router->post("$p/vendors",                VendorController::class . '@store');
$router->get("$p/vendors/{id}",            VendorController::class . '@show');
$router->get("$p/vendors/{id}/edit",       VendorController::class . '@edit');
$router->post("$p/vendors/{id}/edit",      VendorController::class . '@update');
$router->post("$p/vendors/{id}/approve",   VendorController::class . '@approve');
$router->post("$p/vendors/{id}/reject",    VendorController::class . '@reject');
$router->post("$p/vendors/{id}/suspend",   VendorController::class . '@suspend');
$router->post("$p/vendors/{id}/toggle",    VendorController::class . '@toggleStatus');

// ── Customers ─────────────────────────────────────────────────
$router->get("$p/customers",              CustomerController::class . '@index');
$router->get("$p/customers/create",       CustomerController::class . '@create');
$router->post("$p/customers",             CustomerController::class . '@store');
$router->get("$p/customers/{id}",         CustomerController::class . '@show');
$router->get("$p/customers/{id}/edit",    CustomerController::class . '@edit');
$router->post("$p/customers/{id}/edit",   CustomerController::class . '@update');
$router->post("$p/customers/{id}/toggle", CustomerController::class . '@toggleStatus');

// ── Products ──────────────────────────────────────────────────
$router->get("$p/products",               ProductController::class . '@index');
$router->get("$p/products/create",        ProductController::class . '@create');
$router->post("$p/products",              ProductController::class . '@store');
$router->get("$p/products/{id}",          ProductController::class . '@show');
$router->get("$p/products/{id}/edit",     ProductController::class . '@edit');
$router->post("$p/products/{id}/edit",    ProductController::class . '@update');
$router->post("$p/products/{id}/status",  ProductController::class . '@updateStatus');
$router->post("$p/products/{id}/delete",   ProductController::class . '@delete');

// ── Orders ────────────────────────────────────────────────────
$router->get("$p/orders",             OrderController::class . '@index');
$router->get("$p/orders/{id}",        OrderController::class . '@show');
$router->post("$p/orders/{id}/status", OrderController::class . '@updateStatus');

// ── Sub-Admins ────────────────────────────────────────────────
$router->get("$p/sub-admins",              SubAdminController::class . '@index');
$router->get("$p/sub-admins/create",       SubAdminController::class . '@create');
$router->post("$p/sub-admins",             SubAdminController::class . '@store');
$router->get("$p/sub-admins/{id}/edit",    SubAdminController::class . '@edit');
$router->post("$p/sub-admins/{id}",        SubAdminController::class . '@update');
$router->post("$p/sub-admins/{id}/delete", SubAdminController::class . '@delete');
$router->post("$p/sub-admins/{id}/toggle", SubAdminController::class . '@toggleStatus');

// ── Banners ───────────────────────────────────────────────────
$router->get("$p/banners",              BannerController::class . '@index');
$router->get("$p/banners/create",       BannerController::class . '@create');
$router->post("$p/banners",             BannerController::class . '@store');
$router->get("$p/banners/{id}/edit",    BannerController::class . '@edit');
$router->post("$p/banners/{id}",        BannerController::class . '@update');
$router->post("$p/banners/{id}/delete",  BannerController::class . '@delete');
$router->post("$p/banners/{id}/toggle", BannerController::class . '@toggleStatus');

// ── Info Pages (storefront Info Center CMS) ───────────────────
$router->get("$p/pages",              PageController::class . '@index');
$router->get("$p/pages/create",       PageController::class . '@create');
$router->post("$p/pages",             PageController::class . '@store');
$router->get("$p/pages/{id}/edit",    PageController::class . '@edit');
$router->post("$p/pages/{id}",        PageController::class . '@update');
$router->post("$p/pages/{id}/delete", PageController::class . '@delete');
$router->post("$p/pages/{id}/toggle", PageController::class . '@toggleStatus');

// ── Reviews ───────────────────────────────────────────────────
$router->get("$p/reviews",                ReviewController::class . '@index');
$router->post("$p/reviews/{id}/approve",  ReviewController::class . '@approve');
$router->post("$p/reviews/{id}/reject",   ReviewController::class . '@reject');
$router->post("$p/reviews/{id}/delete",   ReviewController::class . '@delete');

// ── Categories ────────────────────────────────────────────────
$router->get("$p/categories",              CategoryController::class . '@index');
$router->get("$p/categories/create",       CategoryController::class . '@create');
$router->post("$p/categories",             CategoryController::class . '@store');
$router->get("$p/categories/{id}/edit",    CategoryController::class . '@edit');
$router->post("$p/categories/{id}",        CategoryController::class . '@update');
$router->post("$p/categories/{id}/delete", CategoryController::class . '@delete');
$router->post("$p/categories/{id}/toggle", CategoryController::class . '@toggle');

// ── Payments ──────────────────────────────────────────────────
$router->get("$p/payments",              PaymentController::class . '@index');
$router->post("$p/payments/{id}/status", PaymentController::class . '@updateStatus');

// ── Activity Logs ─────────────────────────────────────────────
$router->get("$p/activity", ActivityController::class . '@index');

// ── Settings ──────────────────────────────────────────────────
$router->get("$p/settings",  SettingsController::class . '@index');
$router->post("$p/settings", SettingsController::class . '@update');

// ── Reports ───────────────────────────────────────────────────
$router->get("$p/reports",            ReportController::class . '@index');
$router->get("$p/reports/sales",      ReportController::class . '@sales');
$router->get("$p/reports/commission", ReportController::class . '@commission');
$router->get("$p/reports/vendors",    ReportController::class . '@vendors');

// ── Settlement Engine ────────────────────────────────────────
$router->get("$p/settlements",                     SettlementController::class . '@index');
$router->post("$p/settlements/run",                SettlementController::class . '@runPass');
$router->post("$p/settlements/credit",             SettlementController::class . '@credit');
$router->get("$p/settlements/withdrawals",         SettlementController::class . '@withdrawals');
$router->post("$p/settlements/withdrawals/{id}/approve",  SettlementController::class . '@approveWithdrawal');
$router->post("$p/settlements/withdrawals/{id}/reject",   SettlementController::class . '@rejectWithdrawal');
$router->post("$p/settlements/withdrawals/{id}/mark-paid", SettlementController::class . '@markWithdrawalPaid');

// ── Returns / Replacements / Cancellations ───────────────────
$router->get("$p/returns",                  ReturnController::class . '@index');
$router->post("$p/returns/{id}/approve",    ReturnController::class . '@approve');
$router->post("$p/returns/{id}/reject",     ReturnController::class . '@reject');
$router->post("$p/returns/{id}/refund",     ReturnController::class . '@markRefunded');

// ── Disputes ──────────────────────────────────────────────────
$router->get("$p/disputes",             DisputeController::class . '@index');
$router->post("$p/disputes/{id}/resolve", DisputeController::class . '@resolve');

// ── Coupons ───────────────────────────────────────────────────
$router->get("$p/coupons",             CouponController::class . '@index');
$router->get("$p/coupons/create",      CouponController::class . '@create');
$router->post("$p/coupons/create",     CouponController::class . '@store');
$router->get("$p/coupons/{id}/edit",   CouponController::class . '@edit');
$router->post("$p/coupons/{id}/edit",  CouponController::class . '@update');
$router->post("$p/coupons/{id}/delete",CouponController::class . '@delete');
$router->post("$p/coupons/{id}/toggle",CouponController::class . '@toggle');

// ── Gift Vouchers ────────────────────────────────────────────
$router->get("$p/gift-cards",          GiftCardController::class . '@index');
$router->get("$p/gift-cards/create",   GiftCardController::class . '@create');
$router->post("$p/gift-cards/create",  GiftCardController::class . '@store');
$router->get("$p/gift-cards/{id}",     GiftCardController::class . '@show');

// ── Brands ────────────────────────────────────────────────────
$router->get("$p/brands",              BrandController::class . '@index');
$router->get("$p/brands/create",       BrandController::class . '@create');
$router->post("$p/brands/create",      BrandController::class . '@store');
$router->get("$p/brands/{id}/edit",    BrandController::class . '@edit');
$router->post("$p/brands/{id}/edit",   BrandController::class . '@update');
$router->post("$p/brands/{id}/delete", BrandController::class . '@delete');
$router->post("$p/brands/{id}/toggle", BrandController::class . '@toggle');

// ── GST Invoices ──────────────────────────────────────────────
$router->get("$p/invoices",                 InvoiceController::class . '@index');
$router->get("$p/invoices/{id}/download",   InvoiceController::class . '@download');
$router->get("$p/invoices/export",          InvoiceController::class . '@export');
$router->post("$p/invoices/order/{orderId}/regenerate", InvoiceController::class . '@regenerate');

// ── Shipments ─────────────────────────────────────────────────
$router->get("$p/shipments",              ShipmentController::class . '@index');
$router->post("$p/shipments/{id}/track",  ShipmentController::class . '@updateTracking');

return $router;
