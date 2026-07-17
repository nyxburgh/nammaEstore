# Namma E Store — Setup Guide (XAMPP)

## Database — confirmed final state

**49 tables total**, verified with zero duplicate definitions and
balanced SQL syntax (`database/full_install.sql`). Covers: users/
admins/RBAC, products/categories/brands, cart/orders/order-items,
vendor settlement (wallets, settlements, withdrawals), coupons/gift
cards/customer wallet, GST invoicing, shipments/notifications, OTP
verification, loyalty points, and rate-limiting/security tables.

This zip reflects everything built through **Phase 7**: Architecture
(Phase 0), Vendor Settlement (Phase 1), Coupons/Gift Cards/Wallet
(Phase 3), GST & Invoicing (Phase 4), Shipping/Notifications/Inventory
(Phase 5), Security/SEO hardening (Phase 6), vendor email-OTP
verification, and PWA + Loyalty Points (Phase 7).

Phase 7 items **not** built — deliberately, since the spec itself
defers them and they'd need real requirements/infrastructure
decisions first: multi-warehouse, multi-currency, multi-language,
native apps, GraphQL, microservices, AI search, live commerce,
auction, B2B module, dropshipping, ERP/accounting integration.

## 1. Place the files

Extract this zip into:
```
C:\xampp\htdocs\mycart\      (Windows)
/opt/lampp/htdocs/mycart/    (Linux)
```
So that `htdocs/mycart/index.php` exists directly (not nested one level deeper).

## 2. Create the database

**Option A — one file (recommended for hosting a fresh server):**
1. Open phpMyAdmin → **New** → name it `mycart_marketplace` → Create.
2. Select it → **SQL** tab → paste the contents of
   `database/full_install.sql` → Go. This is `database.sql` plus all
   7 migrations already combined in the correct order — 49 tables in
   one import, nothing else to run.

**Option B — step by step (if you already have data in an existing install):**
1. Open phpMyAdmin → **New** → name it `mycart_marketplace` → Create.
2. Select it → **SQL** tab → paste the contents of `database.sql` → Go.
3. Then run each file in `database/migrations/` **in order**:
   - `002_settlement_engine.sql`
   - `003_coupons_giftcards_wallet.sql`
   - `004_gst_invoicing.sql`
   - `005_shipping_notifications_inventory.sql`
   - `006_security_hardening.sql`
   - `007_otp_verification.sql`
   - `008_loyalty_points.sql`

## 3. Install Composer dependencies (PDF invoices + SMTP email)

In a terminal inside the `mycart` folder:
```
composer install
```
Can't be run from my side (no Packagist access in my sandbox — only
your machine has it). **The app works without this step** — invoices
generate as styled HTML instead of PDF, and email falls back to PHP's
basic `mail()` until PHPMailer is installed.

## 4. Configure environment (optional for local use)

Nothing required for XAMPP — every setting has a safe local default.
Only create a `.env` file (copy `.env.example`) to test real
payment/SMS/email/WhatsApp provider credentials.

## 5. Browse the app

- **Storefront:** http://localhost/mycart/
- **Admin panel:** http://localhost/mycart/mc-admin/
- **Vendor panel:** http://localhost/mycart/my-vendor/
- **Sitemap:** http://localhost/mycart/sitemap.xml
- **Robots:** http://localhost/mycart/robots.txt

## 6. Admin login

Seeded account `admin@mycart.com` has a password hash from the
original build with an unknown plaintext. Set a known password via
phpMyAdmin's SQL tab:

```sql
UPDATE mc_admins
SET password = '$2b$12$5ZeUHJlj/zv1XADHyxXiK.xW4x6MCssiSfEO5i/rK6fdilNJSU0yW'
WHERE email = 'admin@mycart.com';
```
Log in with **admin@mycart.com / Admin@123**.

No seeded vendor or customer — register a customer via the
storefront, and create a vendor from **Admin → Vendors → Add Vendor**.

## 7. A realistic end-to-end test path

1. Admin: create 1–2 categories, a brand, and a vendor.
2. Vendor panel: log in as that vendor, add a product — fill in
   **HSN code, GST rate, barcode** (Phase 4/5), set stock low enough
   to test the low-stock notification.
3. Admin → Settings → lower **Settlement → Return Window** to `0`
   days for testing (default 7 — settlement won't look eligible
   otherwise).
4. Admin → Coupons: create a test coupon; Admin → Gift Vouchers:
   issue one.
5. Storefront: register a customer (note the CAPTCHA on the register
   form — Phase 6), add the product to cart, apply the coupon/gift
   card at checkout, place the order.
6. Admin → Orders: mark the order **Delivered** — this sets
   `delivered_at`, which the whole settlement engine depends on (this
   was silently broken before Phase 5 fixed it).
7. Vendor panel → order detail: create a shipment (courier + tracking
   number) — customer gets notified, tracking page updates.
8. Admin → Settlements: **Run Eligibility Pass**, then **Credit
   Eligible to Wallets**.
9. Check: vendor's Wallet page (balance credited), customer's Orders
   → Download/Email GST Invoice, customer's Notifications page.
10. Try a return: from the customer's order detail, request a return;
    Admin → Returns → approve → mark refunded — check the customer's
    wallet got credited and stock was restored.
11. Check the customer's **Loyalty Points** page — points are earned
    automatically when an order is marked Delivered (step 6 above),
    based on Admin → Settings → Loyalty (default: 1 point per ₹100).
    Try redeeming points on a second order at checkout.
12. Visit `/manifest.json` and `/sw.js` directly to confirm the PWA
    endpoints respond — full "Add to Home Screen" testing needs
    actual 192x192 and 512x512 PNG icons placed at
    `assets/frontend/images/pwa-icon-192.png` and `-512.png` (not
    included — these are visual assets, not something to fabricate).

## 8. Security notes for testing

- Login is rate-limited (10 attempts/15 min per email+IP) and
  per-account locked after 5 failed attempts (15 min). If you get
  locked out testing, wait or clear the `mc_rate_limits` table and
  reset `failed_login_attempts`/`locked_until` to 0/NULL on the
  account via phpMyAdmin.
- Customer registration requires solving a simple math CAPTCHA.
- File uploads (product images, banners, brand logos) are now
  MIME-validated server-side — only real jpg/png/gif/webp files under
  5MB will be accepted, regardless of the extension in the filename.

## Known gaps / what's still pending

- **Frontend inline CSS/JS cleanup is partial.** Clean: layout,
  checkout, auth pages, account wallet/returns/notifications/order-
  detail, order tracking. Still has pre-existing inline styles/scripts
  from the original build: `home/index.php`, `products/index.php`,
  `products/show.php`, `cart/index.php`, `vendor/store.php`, and a
  few remaining `account/*.php` pages (orders, profile, addresses,
  wishlist, reviews, overview). Admin/vendor panels are explicitly
  out of scope per your instruction.
- Phase 7 (multi-warehouse, PWA, multi-currency, GraphQL, etc.) not
  started — deliberately deferred, matching the spec's own guidance.
- `composer install` hasn't been run anywhere yet.
- ~~Vendor self-registration currently activates the shop immediately~~
  **Fixed:** admin-created vendors still go live immediately; self-
  registered vendors now must confirm a 6-digit email OTP before
  their shop activates (Vendor Panel → Register → check the email
  configured in your `.env`, or check the `mc_otps` table directly
  in phpMyAdmin if SMTP isn't set up yet — the code is written there
  either way).

