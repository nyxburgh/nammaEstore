<?php
// app/frontend/routes.php

use App\Core\Router;
use App\Frontend\Controllers\HomeController;
use App\Frontend\Controllers\ProductController;
use App\Frontend\Controllers\CartController;
use App\Frontend\Controllers\CheckoutController;
use App\Frontend\Controllers\OrderController;
use App\Frontend\Controllers\AccountController;
use App\Frontend\Controllers\AuthController;
use App\Frontend\Controllers\VendorStoreController;
use App\Frontend\Controllers\InvoiceController;
use App\Frontend\Controllers\SeoController;
use App\Frontend\Controllers\PageController;
use App\Frontend\Controllers\PwaController;
use App\Frontend\Controllers\PaymentController;
use App\Frontend\Controllers\SellController;

$router = new Router();

// ── Home ─────────────────────────────────────────────────────
$router->get('/',                                   HomeController::class . '@index');
$router->post('/subscribe',                         HomeController::class . '@subscribe');

// ── Become a Seller (marketing page — links out to VENDOR_URL) ─
$router->get('/sell',                               SellController::class . '@index');

// ── Auth ─────────────────────────────────────────────────────
$router->get('/login',                              AuthController::class . '@loginForm');
$router->post('/login',                             AuthController::class . '@login');
$router->get('/register',                           AuthController::class . '@registerForm');
$router->post('/register',                          AuthController::class . '@register');
$router->get('/logout',                             AuthController::class . '@logout');
$router->get('/forgot-password',                    AuthController::class . '@forgotForm');
$router->post('/forgot-password',                   AuthController::class . '@forgot');
$router->get('/verify-email',                       AuthController::class . '@verifyForm');
$router->post('/verify-email',                      AuthController::class . '@verify');
$router->post('/verify-email/resend',               AuthController::class . '@resendOtp');
$router->get('/reset-password',                     AuthController::class . '@resetForm');
$router->post('/reset-password',                    AuthController::class . '@reset');

// ── Products ─────────────────────────────────────────────────
$router->get('/products',                           ProductController::class . '@index');
$router->get('/search',                             ProductController::class . '@search');
$router->get('/category/{slug}',                    ProductController::class . '@category');
$router->get('/product/{slug}',                     ProductController::class . '@show');
$router->post('/product/{id}/review',               ProductController::class . '@submitReview');

// ── Cart ─────────────────────────────────────────────────────
$router->get('/cart',                               CartController::class . '@index');
$router->post('/cart/add',                          CartController::class . '@add');
$router->post('/cart/remove',                       CartController::class . '@remove');
$router->post('/cart/update',                       CartController::class . '@update');
$router->get('/cart/count',                         CartController::class . '@count');
$router->post('/cart/clear',                        CartController::class . '@clear');
$router->post('/wishlist/toggle',                   CartController::class . '@toggleWishlist');

// ── Checkout ─────────────────────────────────────────────────
$router->get('/checkout',                           CheckoutController::class . '@index');
$router->post('/checkout/apply-coupon',             CheckoutController::class . '@applyCoupon');
$router->post('/checkout/apply-gift-card',          CheckoutController::class . '@applyGiftCard');
$router->post('/checkout',                          CheckoutController::class . '@process');
$router->get('/checkout/success/{id}',              CheckoutController::class . '@success');

// ── Online Payment (gateway step after checkout) ─────────────
$router->get('/payment/{id}',                       PaymentController::class . '@page');
$router->post('/payment/{id}/callback',             PaymentController::class . '@callback');
$router->post('/payment/{id}/cod',                  PaymentController::class . '@switchToCod');

// ── Order Tracking ───────────────────────────────────────────
$router->get('/track',                              OrderController::class . '@trackForm');
$router->get('/track/{orderNumber}',                OrderController::class . '@track');

// ── Account ──────────────────────────────────────────────────
$router->get('/account',                            AccountController::class . '@overview');
$router->get('/account/orders',                     AccountController::class . '@orders');
$router->get('/account/orders/{id}',                AccountController::class . '@orderDetail');
$router->post('/account/orders/return',             AccountController::class . '@requestReturn');
$router->get('/account/returns',                    AccountController::class . '@returns');
$router->get('/account/wishlist',                   AccountController::class . '@wishlist');
$router->get('/account/addresses',                  AccountController::class . '@addresses');
$router->post('/account/addresses',                 AccountController::class . '@addAddress');
$router->post('/account/addresses/{id}/delete',     AccountController::class . '@deleteAddress');
$router->post('/account/addresses/{id}/default',    AccountController::class . '@setDefaultAddress');
$router->get('/account/notifications',              AccountController::class . '@notifications');
$router->post('/account/notifications/{id}/read',   AccountController::class . '@markNotificationRead');
$router->get('/account/reviews',                    AccountController::class . '@reviews');
$router->get('/account/profile',                    AccountController::class . '@profile');
$router->post('/account/profile',                   AccountController::class . '@updateProfile');
$router->post('/account/profile/password',          AccountController::class . '@updatePassword');
$router->post('/account/profile/password/send-otp', AccountController::class . '@sendPasswordOtp');
$router->get('/account/payments',                   AccountController::class . '@payments');
$router->get('/account/wallet',                     AccountController::class . '@wallet');
$router->get('/account/loyalty',                    AccountController::class . '@loyalty');
$router->get('/invoices/{id}/download',             InvoiceController::class . '@download');
$router->post('/invoices/{id}/email',               InvoiceController::class . '@email');

// ── Info Center (static pages) ───────────────────────────────
$router->get('/info',                               PageController::class . '@index');
$router->post('/info/contact-us',                   PageController::class . '@contactSubmit');
$router->get('/info/{slug}',                        PageController::class . '@show');

// ── SEO ──────────────────────────────────────────────────────
$router->get('/robots.txt',                         SeoController::class . '@robots');
$router->get('/sitemap.xml',                        SeoController::class . '@sitemap');
$router->get('/manifest.json',                      PwaController::class . '@manifest');
$router->get('/sw.js',                              PwaController::class . '@serviceWorker');

// ── Vendor Store Pages ────────────────────────────────────────
// Mode: 'path'  → mycart.com/shop/{slug}
// Mode: 'slug'  → mycart.com/{slug}   (must stay LAST — catches anything)
if (VENDOR_STORE_MODE === 'path') {
    $router->get('/' . VENDOR_STORE_BASE . '/{slug}',
                 VendorStoreController::class . '@show');
} else {
    // Root-slug mode — MUST be last so it doesn't swallow real routes
    $router->get('/{slug}',
                 VendorStoreController::class . '@show');
}

return $router;
