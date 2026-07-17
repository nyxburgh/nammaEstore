# Namma E Store — Multi-Vendor Marketplace

A complete PHP MVC multi-vendor marketplace with three panels:
Admin, Vendor Dashboard, and Customer Storefront.

---

## Tech Stack
- **Backend:** PHP 8.1+ (Custom MVC, no framework)
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** Bootstrap-style custom CSS, DM Sans + Playfair Display
- **Server:** Apache + mod_rewrite (XAMPP recommended)

---

## Installation

### 1. Copy to htdocs
```
cp -r mycart/ /xampp/htdocs/
```

### 2. Create database
```sql
CREATE DATABASE mycart_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import schema + seed
```
mysql -u root mycart_marketplace < database.sql
```

### 4. Configure database (if needed)
Edit `config/database.php`:
```php
'host'     => 'localhost',
'dbname'   => 'mycart_marketplace',
'username' => 'root',
'password' => '',
```

### 5. Visit the site
| URL | Panel |
|-----|-------|
| `http://localhost/mycart/` | Customer Storefront |
| `http://localhost/mycart/mc-admin/` | Admin Panel |
| `http://localhost/mycart/my-vendor/` | Vendor Dashboard |

---

## Default Credentials

### Admin
- **Email:** `admin@mycart.com`
- **Password:** `Admin@123`

---

## Project Structure
```
mycart/
├── .htaccess                    ← Root redirect to public/
├── database.sql                 ← Schema + seed data
├── config/
│   ├── app.php                  ← Constants (APP_URL, DB_PREFIX, etc.)
│   └── database.php             ← DB credentials
├── public/
│   ├── index.php                ← Single entry point
│   ├── .htaccess                ← Apache rewrite rules
│   └── uploads/                 ← User-uploaded files
└── app/
    ├── bootstrap.php            ← Panel routing (admin/vendor/frontend)
    ├── core/                    ← Router, Controller, Model, Auth, DB
    ├── helpers/functions.php    ← currency(), e(), formatDate(), etc.
    ├── models/                  ← Shared models (User, Product, Order...)
    ├── admin/                   ← Admin panel (Controllers/Services/Views)
    ├── frontend/                ← Customer storefront
    └── vendor-panel/            ← Vendor dashboard
```

---

## Features

### Customer Storefront (`/`)
- Product browsing, search, filters, sorting
- Category pages
- Product detail with image gallery, variants, reviews
- Cart (session + DB, guest→user merge on login)
- Checkout with address selection + payment method
- Order tracking by order number
- User account: orders, wishlist, addresses, reviews, payments, profile

### Vendor Dashboard (`/my-vendor/`)
- Registration + login (auto-creates vendor profile + free plan)
- Dashboard: revenue chart, stats, recent orders, top products
- Product management: add, edit, image upload, variants
- Order management: status updates with timeline
- Earnings & commission log breakdown
- Subscription plan overview
- Shop settings

### Admin Panel (`/mc-admin/`)
- Super admin + sub-admins with module permissions
- Vendor management (approve/suspend)
- Product management across all vendors
- Order monitoring
- Reports: sales, commissions, vendors
- Banner/homepage management
- Platform settings
- Activity log

---

## Commission Engine

| Plan | Price | Free Tier | After Threshold |
|------|-------|-----------|-----------------|
| Free | ₹0/mo | None | 10% always |
| Starter | ₹500/mo | ₹10,000/mo | 10% after |
| Growth | ₹1,000/mo | ₹20,000/mo | 10% after |

Commission is calculated per order, tracked per vendor per month.

---

## Customisation

### Change site name
Update in Admin → Settings → `site_name` key.

### Change branding from Namma E Store
The site name is read from `mc_settings.site_name`. No hardcoded strings.

### Change APP_URL
Edit `config/app.php`:
```php
define('APP_URL', 'http://localhost/mycart');
```

---

## Folder Naming Convention
| Prefix | Meaning |
|--------|---------|
| `mc_` | All database tables |
| `mc-admin` | Admin URL prefix |
| `my-vendor` | Vendor URL prefix |

