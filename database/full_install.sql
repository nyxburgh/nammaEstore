-- ═══════════════════════════════════════════════════════════════
-- Namma E Store — Full Database Install (base schema + all migrations)
-- Generated for one-shot hosting deployment.
-- Import this single file instead of database.sql + migrations separately.
-- ═══════════════════════════════════════════════════════════════

-- ───────── BASE SCHEMA (database.sql) ─────────
-- ============================================================
-- MY CART — Multi-Seller Marketplace
-- Database: mycart_marketplace | Prefix: mc_
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

CREATE DATABASE IF NOT EXISTS `mycart_marketplace`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mycart_marketplace`;

-- 1. ADMINS
CREATE TABLE `mc_admins` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `email`       VARCHAR(150) NOT NULL UNIQUE,
  `password`    VARCHAR(255) NOT NULL,
  `role`        ENUM('super_admin','sub_admin') NOT NULL DEFAULT 'sub_admin',
  `avatar`      VARCHAR(255) DEFAULT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `last_login`  DATETIME DEFAULT NULL,
  `created_by`  INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_admin_permissions` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id`    INT UNSIGNED NOT NULL,
  `module`      ENUM('products','payments','reports','activity','banners') NOT NULL,
  `can_view`    TINYINT(1) NOT NULL DEFAULT 1,
  `can_create`  TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit`    TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete`  TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_admin_module` (`admin_id`,`module`),
  CONSTRAINT `fk_perm_admin` FOREIGN KEY (`admin_id`) REFERENCES `mc_admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_activity_logs` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `actor_type`  ENUM('admin','sub_admin','seller','customer') NOT NULL,
  `actor_id`    INT UNSIGNED NOT NULL,
  `actor_name`  VARCHAR(150) NOT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `module`      VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address`  VARCHAR(45) DEFAULT NULL,
  `user_agent`  TEXT DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_actor` (`actor_type`,`actor_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. USERS
CREATE TABLE `mc_users` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`              VARCHAR(150) NOT NULL,
  `email`             VARCHAR(150) NOT NULL UNIQUE,
  `phone`             VARCHAR(20) DEFAULT NULL,
  `password`          VARCHAR(255) NOT NULL,
  `role`              ENUM('customer','seller') NOT NULL DEFAULT 'customer',
  `avatar`            VARCHAR(255) DEFAULT NULL,
  `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
  `is_verified`       TINYINT(1) NOT NULL DEFAULT 0,
  `email_verified_at` DATETIME DEFAULT NULL,
  `remember_token`    VARCHAR(100) DEFAULT NULL,
  `last_login`        DATETIME DEFAULT NULL,
  `created_by`        INT UNSIGNED DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_role` (`role`), KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_user_addresses` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`        INT UNSIGNED NOT NULL,
  `label`          VARCHAR(50) DEFAULT 'Home',
  `recipient_name` VARCHAR(150) NOT NULL,
  `phone`          VARCHAR(20) NOT NULL,
  `address_line1`  VARCHAR(255) NOT NULL,
  `address_line2`  VARCHAR(255) DEFAULT NULL,
  `city`           VARCHAR(100) NOT NULL,
  `state`          VARCHAR(100) NOT NULL,
  `pincode`        VARCHAR(20) NOT NULL,
  `country`        VARCHAR(100) NOT NULL DEFAULT 'India',
  `is_default`     TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_addr_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. SELLER PROFILES & SUBSCRIPTIONS
CREATE TABLE `mc_seller_profiles` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`        INT UNSIGNED NOT NULL UNIQUE,
  `shop_name`      VARCHAR(200) NOT NULL,
  `shop_slug`      VARCHAR(200) NOT NULL UNIQUE,
  `shop_logo`      VARCHAR(255) DEFAULT NULL,
  `shop_banner`    VARCHAR(255) DEFAULT NULL,
  `description`    TEXT DEFAULT NULL,
  `gst_number`     VARCHAR(50) DEFAULT NULL,
  `pan_number`     VARCHAR(50) DEFAULT NULL,
  `bank_account`   VARCHAR(50) DEFAULT NULL,
  `bank_ifsc`      VARCHAR(20) DEFAULT NULL,
  `bank_name`      VARCHAR(100) DEFAULT NULL,
  `status`         ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending',
  `rejection_note` TEXT DEFAULT NULL,
  `approved_by`    INT UNSIGNED DEFAULT NULL,
  `approved_at`    DATETIME DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vp_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_subscription_plans` (
  `id`                         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`                       VARCHAR(100) NOT NULL,
  `slug`                       VARCHAR(100) NOT NULL UNIQUE,
  `price`                      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `commission_pct`             DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `free_threshold`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `commission_after_threshold` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `billing_cycle`              ENUM('monthly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
  `description`                TEXT DEFAULT NULL,
  `is_active`                  TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order`                 INT NOT NULL DEFAULT 0,
  `created_at`                 DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                 DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_seller_subscriptions` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `seller_id`   INT UNSIGNED NOT NULL,
  `plan_id`     INT UNSIGNED NOT NULL,
  `status`      ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `started_at`  DATETIME NOT NULL,
  `expires_at`  DATETIME DEFAULT NULL,
  `payment_ref` VARCHAR(100) DEFAULT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vs_seller` FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vs_plan` FOREIGN KEY (`plan_id`) REFERENCES `mc_subscription_plans`(`id`),
  KEY `idx_seller_status` (`seller_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_seller_monthly_turnover` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `seller_id`      INT UNSIGNED NOT NULL,
  `month`          TINYINT UNSIGNED NOT NULL,
  `year`           SMALLINT UNSIGNED NOT NULL,
  `total_turnover` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `total_orders`   INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_seller_month_year` (`seller_id`,`month`,`year`),
  CONSTRAINT `fk_vmt_seller` FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. CATEGORIES
CREATE TABLE `mc_categories` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id`   INT UNSIGNED DEFAULT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `slug`        VARCHAR(150) NOT NULL UNIQUE,
  `image`       VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_parent` (`parent_id`),
  CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `mc_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. PRODUCTS
CREATE TABLE `mc_products` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `seller_id`           INT UNSIGNED NOT NULL,
  `category_id`         INT UNSIGNED DEFAULT NULL,
  `name`                VARCHAR(255) NOT NULL,
  `slug`                VARCHAR(255) NOT NULL UNIQUE,
  `sku`                 VARCHAR(100) DEFAULT NULL UNIQUE,
  `description`         TEXT DEFAULT NULL,
  `short_description`   TEXT DEFAULT NULL,
  `price`               DECIMAL(12,2) NOT NULL,
  `sale_price`          DECIMAL(12,2) DEFAULT NULL,
  `cost_price`          DECIMAL(12,2) DEFAULT NULL,
  `stock`               INT NOT NULL DEFAULT 0,
  `low_stock_threshold` INT NOT NULL DEFAULT 5,
  `weight`              DECIMAL(8,3) DEFAULT NULL,
  `status`              ENUM('draft','active','inactive','rejected') NOT NULL DEFAULT 'draft',
  `is_featured`         TINYINT(1) NOT NULL DEFAULT 0,
  `rejection_note`      TEXT DEFAULT NULL,
  `approved_by`         INT UNSIGNED DEFAULT NULL,
  `approved_at`         DATETIME DEFAULT NULL,
  `views`               INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_prod_seller` FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `mc_categories`(`id`) ON DELETE SET NULL,
  KEY `idx_seller` (`seller_id`), KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_product_images` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`  INT UNSIGNED NOT NULL,
  `image_path`  VARCHAR(255) NOT NULL,
  `alt_text`    VARCHAR(255) DEFAULT NULL,
  `is_primary`  TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order`  INT NOT NULL DEFAULT 0,
  CONSTRAINT `fk_pimg_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_product_variants` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`     INT UNSIGNED NOT NULL,
  `variant_name`   VARCHAR(100) NOT NULL,
  `variant_value`  VARCHAR(100) NOT NULL,
  `price_modifier` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`          INT NOT NULL DEFAULT 0,
  `sku`            VARCHAR(100) DEFAULT NULL,
  `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT `fk_pvar_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. CART & WISHLIST
CREATE TABLE `mc_carts` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `session_id`  VARCHAR(100) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_user` (`user_id`), KEY `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_cart_items` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cart_id`     INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED NOT NULL,
  `variant_id`  INT UNSIGNED DEFAULT NULL,
  `quantity`    INT NOT NULL DEFAULT 1,
  `price`       DECIMAL(12,2) NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `mc_carts`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ci_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_wishlists` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  CONSTRAINT `fk_wl_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wl_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. ORDERS
CREATE TABLE `mc_orders` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number`     VARCHAR(50) NOT NULL UNIQUE,
  `user_id`          INT UNSIGNED NOT NULL,
  `shipping_name`    VARCHAR(150) NOT NULL,
  `shipping_phone`   VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_city`    VARCHAR(100) NOT NULL,
  `shipping_state`   VARCHAR(100) NOT NULL,
  `shipping_pincode` VARCHAR(20) NOT NULL,
  `payment_method`   ENUM('cod','online','wallet') NOT NULL DEFAULT 'cod',
  `payment_status`   ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status`     ENUM('placed','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'placed',
  `subtotal`         DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `shipping_charge`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total`            DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `notes`            TEXT DEFAULT NULL,
  `placed_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ord_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`),
  KEY `idx_user` (`user_id`), KEY `idx_status` (`order_status`), KEY `idx_placed` (`placed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_order_items` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`      INT UNSIGNED NOT NULL,
  `product_id`    INT UNSIGNED NOT NULL,
  `seller_id`     INT UNSIGNED NOT NULL,
  `variant_id`    INT UNSIGNED DEFAULT NULL,
  `product_name`  VARCHAR(255) NOT NULL,
  `variant_label` VARCHAR(100) DEFAULT NULL,
  `quantity`      INT NOT NULL,
  `unit_price`    DECIMAL(12,2) NOT NULL,
  `subtotal`      DECIMAL(14,2) NOT NULL,
  `status`        ENUM('placed','confirmed','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'placed',
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
  KEY `idx_order` (`order_id`), KEY `idx_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_order_seller_splits` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`          INT UNSIGNED NOT NULL,
  `seller_id`         INT UNSIGNED NOT NULL,
  `gross_amount`      DECIMAL(14,2) NOT NULL,
  `commission_pct`    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `commission_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `seller_earning`    DECIMAL(12,2) NOT NULL,
  `payout_status`     ENUM('pending','processing','paid','on_hold') NOT NULL DEFAULT 'pending',
  `payout_date`       DATETIME DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_order_seller` (`order_id`,`seller_id`),
  CONSTRAINT `fk_ovs_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
  KEY `idx_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_order_status_timeline` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`        INT UNSIGNED NOT NULL,
  `status`          VARCHAR(50) NOT NULL,
  `note`            TEXT DEFAULT NULL,
  `changed_by_type` ENUM('admin','seller','system') NOT NULL DEFAULT 'system',
  `changed_by_id`   INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ost_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. PAYMENTS & COMMISSIONS
CREATE TABLE `mc_payments` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`         INT UNSIGNED NOT NULL,
  `user_id`          INT UNSIGNED NOT NULL,
  `transaction_id`   VARCHAR(150) DEFAULT NULL UNIQUE,
  `gateway`          VARCHAR(50) DEFAULT NULL,
  `amount`           DECIMAL(14,2) NOT NULL,
  `currency`         VARCHAR(10) NOT NULL DEFAULT 'INR',
  `status`           ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` JSON DEFAULT NULL,
  `paid_at`          DATETIME DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`),
  KEY `idx_order` (`order_id`), KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_commissions` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`          INT UNSIGNED NOT NULL,
  `order_item_id`     INT UNSIGNED NOT NULL,
  `seller_id`         INT UNSIGNED NOT NULL,
  `product_id`        INT UNSIGNED NOT NULL,
  `plan_id`           INT UNSIGNED DEFAULT NULL,
  `plan_name`         VARCHAR(100) NOT NULL,
  `item_amount`       DECIMAL(12,2) NOT NULL,
  `turnover_before`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `commission_pct`    DECIMAL(5,2) NOT NULL,
  `commission_amount` DECIMAL(12,2) NOT NULL,
  `seller_earning`    DECIMAL(12,2) NOT NULL,
  `calculated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_seller` (`seller_id`), KEY `idx_order` (`order_id`), KEY `idx_calc` (`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. REVIEWS
CREATE TABLE `mc_reviews` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `order_id`    INT UNSIGNED DEFAULT NULL,
  `rating`      TINYINT UNSIGNED NOT NULL,
  `title`       VARCHAR(200) DEFAULT NULL,
  `body`        TEXT DEFAULT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `is_flagged`  TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_prod_ord` (`user_id`,`product_id`,`order_id`),
  CONSTRAINT `fk_rev_prod` FOREIGN KEY (`product_id`) REFERENCES `mc_products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. BANNERS
CREATE TABLE `mc_banners` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(200) NOT NULL,
  `image_path`  VARCHAR(255) NOT NULL,
  `link_url`    VARCHAR(500) DEFAULT NULL,
  `position`    ENUM('hero','sidebar','popup','top_bar') NOT NULL DEFAULT 'hero',
  `sort_order`  INT NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `starts_at`   DATETIME DEFAULT NULL,
  `ends_at`     DATETIME DEFAULT NULL,
  `created_by`  INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_position` (`position`), KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. SETTINGS
CREATE TABLE `mc_settings` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`         VARCHAR(100) NOT NULL UNIQUE,
  `value`       TEXT DEFAULT NULL,
  `group`       VARCHAR(50) NOT NULL DEFAULT 'general',
  `label`       VARCHAR(200) DEFAULT NULL,
  `type`        ENUM('text','number','boolean','json','file') NOT NULL DEFAULT 'text',
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. PASSWORD RESETS
CREATE TABLE `mc_password_resets` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email`       VARCHAR(150) NOT NULL,
  `token`       VARCHAR(255) NOT NULL,
  `user_type`   ENUM('admin','user') NOT NULL DEFAULT 'user',
  `expires_at`  DATETIME NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_email_token` (`email`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Super Admin (password: Admin@123)
INSERT INTO `mc_admins` (`name`,`email`,`password`,`role`,`is_active`) VALUES
('Super Admin','admin@mycart.com','$2y$12$05jI66oNhr6kverzc.FfD.bP6.7OgvfhWH.RoHtkLu4nRXvgR1jXK','super_admin',1);

-- Subscription Plans
INSERT INTO `mc_subscription_plans` (`name`,`slug`,`price`,`commission_pct`,`free_threshold`,`commission_after_threshold`,`billing_cycle`,`description`,`sort_order`) VALUES
('Free Plan',   'free',     0.00, 10.00,     0.00, 10.00, 'lifetime', '10% commission on all sales',             1),
('Starter Plan','starter', 500.00, 0.00, 10000.00, 10.00, 'monthly',  '0% commission up to ₹10,000/month',       2),
('Growth Plan', 'growth', 1000.00, 0.00, 20000.00, 10.00, 'monthly',  '0% commission up to ₹20,000/month',       3);

-- Settings
INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
('site_name',          'Namma E Store',         'general',    'Site Name',            'text'),
('site_email',         'info@mycart.com', 'general',    'Contact Email',        'text'),
('site_phone',         '+91 9999999999',  'general',    'Contact Phone',        'text'),
('currency',           'INR',             'general',    'Currency Code',        'text'),
('currency_symbol',    '₹',               'general',    'Currency Symbol',      'text'),
('default_commission', '10',              'commission', 'Default Commission %', 'number'),
('maintenance_mode',   '0',               'general',    'Maintenance Mode',     'boolean'),
('order_prefix',       'MC',              'orders',     'Order Number Prefix',  'text');

-- Categories
INSERT INTO `mc_categories` (`name`,`slug`,`sort_order`,`is_active`) VALUES
('Electronics',    'electronics',   1, 1),
('Fashion',        'fashion',       2, 1),
('Home & Kitchen', 'home-kitchen',  3, 1),
('Sports',         'sports',        4, 1),
('Books',          'books',         5, 1);

-- ─── Seller profile extensions ──────────────────────────────
ALTER TABLE `mc_seller_profiles`
  ADD COLUMN IF NOT EXISTS `shop_phone`   VARCHAR(20) DEFAULT NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `address`      VARCHAR(255) DEFAULT NULL AFTER `shop_phone`,
  ADD COLUMN IF NOT EXISTS `city`         VARCHAR(100) DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `state`        VARCHAR(100) DEFAULT NULL AFTER `city`,
  ADD COLUMN IF NOT EXISTS `pincode`      VARCHAR(20) DEFAULT NULL AFTER `state`,
  ADD COLUMN IF NOT EXISTS `bank_account_name`   VARCHAR(150) DEFAULT NULL AFTER `bank_name`,
  ADD COLUMN IF NOT EXISTS `bank_account_number` VARCHAR(50) DEFAULT NULL AFTER `bank_account_name`;
-- ─── Additional categories ─────────────────────────────────
INSERT IGNORE INTO `mc_categories` (`name`,`slug`,`sort_order`,`is_active`) VALUES
('Beauty',    'beauty',   6, 1),
('Grocery',   'grocery',  7, 1),
('Gaming',    'gaming',   8, 1);


-- ───────── MIGRATION: 002_settlement_engine.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 002: Seller Settlement Engine (Phase 1)
-- ═══════════════════════════════════════════════════════════════
-- Run this AFTER the base database.sql has already been imported.
-- In phpMyAdmin (XAMPP): select mycart_marketplace → SQL tab →
-- paste this file's contents → Go.
--
-- What this adds:
--   • Explicit subscription-vs-commission seller split (was a
--     turnover-threshold heuristic before — now matches the spec's
--     two genuinely separate settlement paths)
--   • Return window tracking (orders.delivered_at)
--   • Returns/replacements/cancellations (a hard dependency of
--     settlement — money can't be released until the return window
--     is provably closed)
--   • Disputes (settlement is blocked while a dispute is open)
--   • Seller wallet + a full audit-ledger of every balance change
--   • Seller settlements (the per-order deduction waterfall)
--   • Seller withdrawals (payout requests: UPI/Bank/Wallet,
--     manual or auto approval)
--   • Seller KYC documents (GST cert, PAN, cancelled cheque, etc.)
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Explicit seller type ──────────────────────────────────
-- 'subscription' = pays a plan fee, 0% commission while active,
--                   settlement BLOCKED entirely if subscription lapses.
-- 'commission'   = no subscription, a % is deducted from every order,
--                   settlement is never blocked by subscription status.
ALTER TABLE `mc_seller_profiles`
    ADD COLUMN `seller_type` ENUM('subscription','commission') NOT NULL DEFAULT 'commission' AFTER `status`;

-- ── 2. Delivery timestamp (return-window anchor) ─────────────
ALTER TABLE `mc_orders`
    ADD COLUMN `delivered_at` DATETIME DEFAULT NULL AFTER `placed_at`;

-- ── 3. Returns / Replacements / Cancellations ────────────────
CREATE TABLE `mc_returns` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`       INT UNSIGNED NOT NULL,
    `order_item_id`  INT UNSIGNED NOT NULL,
    `user_id`        INT UNSIGNED NOT NULL,
    `seller_id`      INT UNSIGNED NOT NULL,
    `type`           ENUM('return','replacement','cancel') NOT NULL DEFAULT 'return',
    `reason`         VARCHAR(255) NOT NULL,
    `note`           TEXT DEFAULT NULL,
    `status`         ENUM('requested','approved','rejected','picked_up','refunded','completed') NOT NULL DEFAULT 'requested',
    `refund_amount`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `requested_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `resolved_at`    DATETIME DEFAULT NULL,
    `resolved_by`    INT UNSIGNED DEFAULT NULL,
    CONSTRAINT `fk_ret_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ret_item` FOREIGN KEY (`order_item_id`) REFERENCES `mc_order_items`(`id`) ON DELETE CASCADE,
    KEY `idx_seller` (`seller_id`), KEY `idx_status` (`status`), KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Disputes ───────────────────────────────────────────────
-- A minimal flag-level table for Phase 0/1 — settlement checks
-- "no open dispute" against this. Full dispute workflow (messages,
-- evidence, admin mediation) is a later phase.
CREATE TABLE `mc_disputes` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`    INT UNSIGNED NOT NULL,
    `seller_id`   INT UNSIGNED NOT NULL,
    `raised_by`   ENUM('customer','seller','admin') NOT NULL,
    `raised_by_id` INT UNSIGNED NOT NULL,
    `reason`      VARCHAR(255) NOT NULL,
    `status`      ENUM('open','resolved') NOT NULL DEFAULT 'open',
    `resolution_note` TEXT DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_disp_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_seller_status` (`seller_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. Seller wallet ──────────────────────────────────────────
CREATE TABLE `mc_seller_wallets` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `seller_id`        INT UNSIGNED NOT NULL UNIQUE,
    `balance`          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_earned`     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_withdrawn`  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_vw_seller` FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Full audit trail of every balance change — required for any
-- financial ledger, and for resolving seller payout disputes.
CREATE TABLE `mc_seller_wallet_transactions` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `seller_id`        INT UNSIGNED NOT NULL,
    `type`             ENUM('credit','debit') NOT NULL,
    `amount`           DECIMAL(12,2) NOT NULL,
    `balance_after`    DECIMAL(14,2) NOT NULL,
    `reference_type`   ENUM('settlement','withdrawal','penalty','refund_deduction','adjustment') NOT NULL,
    `reference_id`     INT UNSIGNED DEFAULT NULL,
    `description`      VARCHAR(255) DEFAULT NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_seller` (`seller_id`), KEY `idx_reference` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. Seller settlements ─────────────────────────────────────
-- One row per order_seller_split that has become (or was evaluated
-- to become) eligible. Holds the full deduction waterfall from the
-- spec: Order Amount − Tax − Shipping − Platform Charges −
-- Commission − Penalty − Refund − Subscription Due = Seller Payable.
CREATE TABLE `mc_seller_settlements` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `seller_id`            INT UNSIGNED NOT NULL,
    `order_id`             INT UNSIGNED NOT NULL,
    `order_seller_split_id` INT UNSIGNED NOT NULL,
    `gross_amount`         DECIMAL(12,2) NOT NULL,
    `tax_amount`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `platform_charge`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `commission_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `penalty_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `refund_amount`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `subscription_due`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `net_payable`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status`               ENUM('not_eligible','eligible','credited','on_hold') NOT NULL DEFAULT 'not_eligible',
    `hold_reason`          VARCHAR(255) DEFAULT NULL,
    `eligible_at`          DATETIME DEFAULT NULL,
    `credited_at`          DATETIME DEFAULT NULL,
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_split` (`order_seller_split_id`),
    CONSTRAINT `fk_settle_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_seller_status` (`seller_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 7. Seller withdrawals (payout requests) ──────────────────
CREATE TABLE `mc_seller_withdrawals` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `seller_id`       INT UNSIGNED NOT NULL,
    `amount`          DECIMAL(12,2) NOT NULL,
    `method`          ENUM('upi','bank_transfer','wallet') NOT NULL,
    `method_details`  VARCHAR(255) DEFAULT NULL,
    `status`          ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
    `admin_note`      VARCHAR(255) DEFAULT NULL,
    `requested_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at`    DATETIME DEFAULT NULL,
    `processed_by`    INT UNSIGNED DEFAULT NULL,
    KEY `idx_seller_status` (`seller_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 8. Seller KYC documents ───────────────────────────────────
CREATE TABLE `mc_seller_documents` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `seller_id`    INT UNSIGNED NOT NULL,
    `doc_type`     ENUM('gst_certificate','pan_card','cancelled_cheque','shop_license','other') NOT NULL,
    `file_path`    VARCHAR(255) NOT NULL,
    `status`       ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `uploaded_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `verified_by`  INT UNSIGNED DEFAULT NULL,
    `verified_at`  DATETIME DEFAULT NULL,
    KEY `idx_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 9. Settlement-related config (key/value in existing settings table) ──
INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
    ('settlement_min_withdrawal',      '1000', 'settlement', 'Minimum Withdrawal Amount (₹)',    'number'),
    ('settlement_return_window_days',  '7',    'settlement', 'Return Window (days after delivery)', 'number'),
    ('settlement_auto_approve',        '0',    'settlement', 'Auto-approve withdrawals under threshold', 'boolean'),
    ('settlement_platform_charge_pct', '0',    'settlement', 'Flat Platform Charge (%)',          'number')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ── 10. Backfill existing sellers ────────────────────────────
-- Anyone currently on a paid plan is treated as a subscription
-- seller going forward; everyone else defaults to commission
-- (matches the ALTER TABLE default, this just makes it explicit
-- for sellers that already have an active paid subscription).
UPDATE `mc_seller_profiles` vp
JOIN `mc_seller_subscriptions` vs ON vs.seller_id = vp.user_id AND vs.status = 'active'
JOIN `mc_subscription_plans` sp ON sp.id = vs.plan_id AND sp.price > 0
SET vp.seller_type = 'subscription';

-- Give every existing seller a wallet row so lookups never need
-- an existence check.
INSERT INTO `mc_seller_wallets` (seller_id)
SELECT id FROM `mc_users` WHERE role = 'seller'
ON DUPLICATE KEY UPDATE seller_id = seller_id;


-- ───────── MIGRATION: 003_coupons_giftcards_wallet.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 003: Coupons, Gift Vouchers, Customer Wallet (Phase 3)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 002_settlement_engine.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • Brands (was entirely missing — needed for "Brand Coupon" to
--     be a real scope rather than a spec item with nothing behind it)
--   • Coupons — all 10 scopes from the spec: fixed/percentage value
--     types, and platform/seller/category/brand/product/user/
--     first-order/festival scopes, sharing one flexible schema
--   • Gift vouchers — company/seller/recharge types, expiry,
--     partial usage with running balance
--   • Customer wallet — balance + full audit ledger, same pattern
--     as the seller wallet from Phase 1
--   • Order-level columns to record which coupon/gift-card/wallet
--     amount applied, so refunds and reporting can trace them back
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Brands ─────────────────────────────────────────────────
CREATE TABLE `mc_brands` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150) NOT NULL,
    `slug`       VARCHAR(160) NOT NULL UNIQUE,
    `logo`       VARCHAR(255) DEFAULT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `mc_products`
    ADD COLUMN `brand_id` INT UNSIGNED DEFAULT NULL AFTER `category_id`,
    ADD CONSTRAINT `fk_prod_brand` FOREIGN KEY (`brand_id`) REFERENCES `mc_brands`(`id`) ON DELETE SET NULL;

-- ── 2. Coupons ────────────────────────────────────────────────
-- One flexible table covers all spec-listed coupon types:
--   value_type: fixed | percentage
--   scope:      platform | seller | category | brand | product |
--               user | first_order | festival
-- scope_id holds the relevant seller/category/brand/product/user id
-- depending on `scope` (NULL for platform/first_order/festival,
-- which apply universally rather than to one target).
CREATE TABLE `mc_coupons` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`                VARCHAR(50) NOT NULL UNIQUE,
    `label`               VARCHAR(150) DEFAULT NULL,
    `value_type`          ENUM('fixed','percentage') NOT NULL DEFAULT 'fixed',
    `value`               DECIMAL(10,2) NOT NULL,
    `max_discount_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'cap for percentage coupons',
    `min_order_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `scope`               ENUM('platform','seller','category','brand','product','user','first_order','festival') NOT NULL DEFAULT 'platform',
    `scope_id`            INT UNSIGNED DEFAULT NULL,
    `usage_limit_total`   INT UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited',
    `usage_limit_per_user` INT UNSIGNED NOT NULL DEFAULT 1,
    `used_count`          INT UNSIGNED NOT NULL DEFAULT 0,
    `starts_at`           DATETIME DEFAULT NULL,
    `expires_at`          DATETIME DEFAULT NULL,
    `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
    `created_by`          INT UNSIGNED DEFAULT NULL,
    `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_scope` (`scope`,`scope_id`), KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_coupon_usages` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id`        INT UNSIGNED NOT NULL,
    `user_id`          INT UNSIGNED NOT NULL,
    `order_id`         INT UNSIGNED NOT NULL,
    `discount_amount`  DECIMAL(10,2) NOT NULL,
    `used_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cu_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `mc_coupons`(`id`) ON DELETE CASCADE,
    KEY `idx_user_coupon` (`user_id`,`coupon_id`), KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Gift vouchers ──────────────────────────────────────────
CREATE TABLE `mc_gift_cards` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`             VARCHAR(30) NOT NULL UNIQUE,
    `type`             ENUM('company','seller','recharge') NOT NULL DEFAULT 'company',
    `seller_id`        INT UNSIGNED DEFAULT NULL COMMENT 'set only for type=seller',
    `initial_balance`  DECIMAL(10,2) NOT NULL,
    `current_balance`  DECIMAL(10,2) NOT NULL,
    `issued_to_email`  VARCHAR(150) DEFAULT NULL,
    `issued_to_user_id` INT UNSIGNED DEFAULT NULL,
    `purchased_by`     INT UNSIGNED DEFAULT NULL,
    `status`           ENUM('active','redeemed','expired','inactive') NOT NULL DEFAULT 'active',
    `expires_at`       DATETIME DEFAULT NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`), KEY `idx_issued_user` (`issued_to_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_gift_card_transactions` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gift_card_id`   INT UNSIGNED NOT NULL,
    `type`           ENUM('issue','redeem','refund') NOT NULL,
    `amount`         DECIMAL(10,2) NOT NULL,
    `order_id`       INT UNSIGNED DEFAULT NULL,
    `balance_after`  DECIMAL(10,2) NOT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_gct_card` FOREIGN KEY (`gift_card_id`) REFERENCES `mc_gift_cards`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Customer wallet (same audit-ledger pattern as seller wallet) ──
CREATE TABLE `mc_customer_wallets` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL UNIQUE,
    `balance`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_added`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_used`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cw_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_customer_wallet_transactions` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL,
    `type`            ENUM('credit','debit') NOT NULL,
    `amount`          DECIMAL(10,2) NOT NULL,
    `balance_after`   DECIMAL(12,2) NOT NULL,
    `reference_type`  ENUM('topup','order_payment','refund','gift_card_redeem','admin_adjustment') NOT NULL,
    `reference_id`    INT UNSIGNED DEFAULT NULL,
    `description`     VARCHAR(255) DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. Order-level tracing ────────────────────────────────────
ALTER TABLE `mc_orders`
    ADD COLUMN `coupon_id`         INT UNSIGNED DEFAULT NULL AFTER `discount`,
    ADD COLUMN `coupon_code`       VARCHAR(50) DEFAULT NULL AFTER `coupon_id`,
    ADD COLUMN `gift_card_amount`  DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `coupon_code`,
    ADD COLUMN `wallet_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `gift_card_amount`;

-- ── 6. Backfill wallets for existing customers ───────────────
INSERT INTO `mc_customer_wallets` (user_id)
SELECT id FROM `mc_users` WHERE role = 'customer'
ON DUPLICATE KEY UPDATE user_id = user_id;


-- ───────── MIGRATION: 004_gst_invoicing.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 004: GST & Invoicing (Phase 4)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 003_coupons_giftcards_wallet.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • HSN code + GST rate on products (required to itemize tax on
--     any invoice — was missing entirely)
--   • Seller registered address/state (required for place-of-supply:
--     CGST+SGST for intra-state sales, IGST for inter-state — this
--     was informally patched around in Phase 1's seller settings
--     with a silent try/catch; this migration makes those columns
--     real so GST calculation has something reliable to read)
--   • Company/platform GST identity (for the invoice letterhead)
--   • Invoices + invoice line items, one invoice per seller's
--     portion of an order (matches how Indian marketplaces actually
--     invoice — each seller is the seller-of-record for GST purposes)
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Product tax fields ─────────────────────────────────────
ALTER TABLE `mc_products`
    ADD COLUMN `hsn_code` VARCHAR(20) DEFAULT NULL AFTER `sku`,
    ADD COLUMN `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 18.00 AFTER `hsn_code`;

-- ── 2. Seller registered address (place of supply) ────────────
-- Only added if not already present — Phase 1's SellerSettingsService
-- wrote to these defensively via try/catch in case a prior manual
-- migration had already added them; this is the authoritative version.
ALTER TABLE `mc_seller_profiles`
    ADD COLUMN IF NOT EXISTS `shop_phone` VARCHAR(20) DEFAULT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `address`    VARCHAR(255) DEFAULT NULL AFTER `shop_phone`,
    ADD COLUMN IF NOT EXISTS `city`       VARCHAR(100) DEFAULT NULL AFTER `address`,
    ADD COLUMN IF NOT EXISTS `state`      VARCHAR(100) DEFAULT NULL AFTER `city`,
    ADD COLUMN IF NOT EXISTS `pincode`    VARCHAR(20) DEFAULT NULL AFTER `state`;

-- ── 3. Company GST identity (for invoice letterhead / platform-level docs) ──
INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
    ('company_name',        'Namma E Store Pvt Ltd', 'gst', 'Company Legal Name',      'text'),
    ('company_gst_number',  '',                'gst', 'Company GSTIN',          'text'),
    ('company_address',     '',                'gst', 'Registered Address',     'text'),
    ('company_state',       'Maharashtra',     'gst', 'Registered State',       'text'),
    ('company_pan',         '',                'gst', 'Company PAN',            'text')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ── 4. Invoices ───────────────────────────────────────────────
-- One invoice per (order, seller) pair — mirrors order_seller_splits,
-- since each seller is the seller-of-record for their portion of a
-- multi-seller order and GST law requires the invoice to name the
-- actual seller, not the platform.
CREATE TABLE `mc_invoices` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number`        VARCHAR(50) NOT NULL UNIQUE,
    `order_id`              INT UNSIGNED NOT NULL,
    `order_seller_split_id` INT UNSIGNED NOT NULL,
    `seller_id`             INT UNSIGNED NOT NULL,
    `user_id`               INT UNSIGNED NOT NULL,
    `invoice_date`          DATE NOT NULL,
    `place_of_supply`       VARCHAR(100) NOT NULL,
    `is_interstate`         TINYINT(1) NOT NULL DEFAULT 0,
    `taxable_amount`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `cgst_amount`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sgst_amount`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `igst_amount`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_tax`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `grand_total`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `pdf_path`              VARCHAR(255) DEFAULT NULL,
    `emailed_at`            DATETIME DEFAULT NULL,
    `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_split_invoice` (`order_seller_split_id`),
    CONSTRAINT `fk_inv_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_seller` (`seller_id`), KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_invoice_items` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_id`       INT UNSIGNED NOT NULL,
    `order_item_id`    INT UNSIGNED NOT NULL,
    `product_name`     VARCHAR(255) NOT NULL,
    `hsn_code`         VARCHAR(20) DEFAULT NULL,
    `quantity`         INT NOT NULL,
    `unit_price`       DECIMAL(12,2) NOT NULL,
    `taxable_amount`   DECIMAL(12,2) NOT NULL,
    `gst_rate`         DECIMAL(5,2) NOT NULL,
    `cgst_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `sgst_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `igst_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `line_total`       DECIMAL(12,2) NOT NULL,
    CONSTRAINT `fk_ii_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `mc_invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ───────── MIGRATION: 005_shipping_notifications_inventory.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 005: Shipping, Notifications, Inventory (Phase 5)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 004_gst_invoicing.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • Shipments + shipment tracking timeline, one shipment per
--     (order, seller) pair — matches order_seller_splits, since each
--     seller ships their own portion of a multi-seller order
--     independently with their own courier/tracking number
--   • In-app notifications (customer/seller/admin) — was a fully
--     static placeholder page before this migration
--   • Barcode field on products (SKU already existed)
--   • Low-stock notification toggle
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Product inventory fields ──────────────────────────────
ALTER TABLE `mc_products`
    ADD COLUMN `barcode` VARCHAR(50) DEFAULT NULL AFTER `sku`;

-- ── 2. Shipments ──────────────────────────────────────────────
CREATE TABLE `mc_shipments` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`              INT UNSIGNED NOT NULL,
    `order_seller_split_id` INT UNSIGNED NOT NULL,
    `seller_id`             INT UNSIGNED NOT NULL,
    `courier_name`          VARCHAR(100) NOT NULL,
    `tracking_number`       VARCHAR(100) NOT NULL,
    `tracking_url`          VARCHAR(255) DEFAULT NULL,
    `status`                ENUM('pending','picked_up','in_transit','out_for_delivery','delivered','failed','rto') NOT NULL DEFAULT 'pending',
    `weight_kg`             DECIMAL(6,2) DEFAULT NULL,
    `dimensions`            VARCHAR(50) DEFAULT NULL COMMENT 'LxWxH cm, free text',
    `shipping_charge`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `shipped_at`            DATETIME DEFAULT NULL,
    `delivered_at`          DATETIME DEFAULT NULL,
    `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_split_shipment` (`order_seller_split_id`),
    CONSTRAINT `fk_ship_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_seller` (`seller_id`), KEY `idx_tracking` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_shipment_tracking` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shipment_id`  INT UNSIGNED NOT NULL,
    `status`       VARCHAR(50) NOT NULL,
    `location`     VARCHAR(150) DEFAULT NULL,
    `note`         VARCHAR(255) DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_st_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `mc_shipments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Notifications ──────────────────────────────────────────
CREATE TABLE `mc_notifications` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_type`   ENUM('customer','seller','admin') NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL,
    `type`        VARCHAR(50) NOT NULL COMMENT 'order_placed|order_status|shipment|return|withdrawal|low_stock|dispute|generic',
    `title`       VARCHAR(150) NOT NULL,
    `message`     VARCHAR(255) NOT NULL,
    `link`        VARCHAR(255) DEFAULT NULL,
    `is_read`     TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_type`,`user_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Settings ───────────────────────────────────────────────
INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
    ('low_stock_notifications', '1', 'inventory', 'Notify seller when stock hits threshold', 'boolean'),
    ('default_courier',         'Manual / Self-Ship', 'shipping', 'Default Courier Name', 'text')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);


-- ───────── MIGRATION: 006_security_hardening.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 006: Security Hardening (Phase 6)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 005_shipping_notifications_inventory.sql has already
-- been imported. phpMyAdmin: select mycart_marketplace → SQL tab →
-- paste → Go.
--
-- What this adds:
--   • Login attempt lockout for both customer/seller and admin login
--   • A generic rate-limit table (login throttling, and reusable for
--     any other sensitive endpoint)
--   • Remember-me token support actually wired up — the column
--     existed on mc_users since the original build but no code ever
--     used it
--   • Fixes mc_admin_permissions.module — its ENUM only listed the
--     5 original modules and was never widened when Phases 1 and 3
--     added settlements/returns/disputes/coupons/gift_cards/brands,
--     so granting a sub-admin permission on any of those would have
--     failed silently
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Login lockout ──────────────────────────────────────────
ALTER TABLE `mc_users`
    ADD COLUMN `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `last_login`,
    ADD COLUMN `locked_until` DATETIME DEFAULT NULL AFTER `failed_login_attempts`;

ALTER TABLE `mc_admins`
    ADD COLUMN `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `last_login`,
    ADD COLUMN `locked_until` DATETIME DEFAULT NULL AFTER `failed_login_attempts`,
    ADD COLUMN `remember_token` VARCHAR(100) DEFAULT NULL AFTER `locked_until`;

-- ── 2. Generic rate limiter ───────────────────────────────────
-- Used for login throttling by IP+identifier; reusable for any
-- other endpoint worth rate-limiting later (OTP requests, coupon
-- brute-forcing, etc.) without a new table each time.
CREATE TABLE `mc_rate_limits` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rate_key`      VARCHAR(190) NOT NULL UNIQUE,
    `attempts`      INT UNSIGNED NOT NULL DEFAULT 1,
    `first_attempt_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `blocked_until` DATETIME DEFAULT NULL,
    KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Fix admin_permissions module ENUM ──────────────────────
ALTER TABLE `mc_admin_permissions`
    MODIFY COLUMN `module` ENUM(
        'products','payments','reports','activity','banners',
        'settlements','returns','disputes','coupons','gift_cards','brands'
    ) NOT NULL;


-- ───────── MIGRATION: 007_otp_verification.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 007: OTP Verification (seller self-registration email check)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 006_security_hardening.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • A generic OTP table — used here for seller self-registration
--     email verification, but the `purpose` column keeps it reusable
--     for other OTP flows later (spec also lists "OTP Login") without
--     a new table each time.
--
-- Business rule this implements:
--   • Admin-created sellers go live immediately (unchanged — already
--     the existing behavior).
--   • Self-registered sellers start as unverified/pending and must
--     confirm an emailed OTP before their shop goes live.
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE `mc_otps` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `identifier`  VARCHAR(150) NOT NULL COMMENT 'email or phone the OTP was sent to',
    `purpose`     VARCHAR(50) NOT NULL COMMENT 'seller_email_verify|login|password_reset|...',
    `otp_code`    VARCHAR(10) NOT NULL,
    `attempts`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `is_used`     TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at`  DATETIME NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_lookup` (`identifier`,`purpose`,`is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ───────── MIGRATION: 008_loyalty_points.sql ─────────
-- ═══════════════════════════════════════════════════════════════
-- Migration 008: Loyalty Points (Phase 7)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 007_otp_verification.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- Points are earned on DELIVERED orders (not just placed — mirrors
-- the settlement engine's carefulness: a cancelled or returned order
-- shouldn't have generated a reward), and can be redeemed at
-- checkout for a discount, same UX pattern as the wallet.
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE `mc_loyalty_points` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL UNIQUE,
    `balance`         INT UNSIGNED NOT NULL DEFAULT 0,
    `total_earned`    INT UNSIGNED NOT NULL DEFAULT 0,
    `total_redeemed`  INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lp_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_loyalty_transactions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `type`        ENUM('earned','redeemed','expired','adjusted') NOT NULL,
    `points`      INT NOT NULL COMMENT 'positive for earned/adjusted-up, negative for redeemed/expired',
    `order_id`    INT UNSIGNED DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `mc_orders`
    ADD COLUMN `loyalty_points_used`   INT UNSIGNED NOT NULL DEFAULT 0 AFTER `wallet_amount`,
    ADD COLUMN `loyalty_points_earned` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `loyalty_points_used`,
    ADD COLUMN `loyalty_credited`      TINYINT(1) NOT NULL DEFAULT 0 AFTER `loyalty_points_earned` COMMENT 'guards against double-crediting if delivered status is set more than once';

INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
    ('loyalty_enabled',      '1',   'loyalty', 'Enable Loyalty Points', 'boolean'),
    ('loyalty_earn_rate',    '1',   'loyalty', 'Points earned per ₹100 spent', 'number'),
    ('loyalty_redeem_rate',  '0.5', 'loyalty', 'Rupee value per point redeemed', 'number'),
    ('loyalty_min_redeem',   '100', 'loyalty', 'Minimum points required to redeem', 'number')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

INSERT INTO `mc_loyalty_points` (user_id)
SELECT id FROM `mc_users` WHERE role = 'customer'
ON DUPLICATE KEY UPDATE user_id = user_id;

-- ═══ 010: Info Center pages (admin-managed CMS) ═══
-- 010: Info Center pages become admin-manageable.
-- Content supports tokens replaced at render time:
--   {{app_url}} {{seller_url}} {{site_name}} {{site_email}} {{site_phone}}
CREATE TABLE IF NOT EXISTS `mc_pages` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`              VARCHAR(100) NOT NULL,
  `icon`              VARCHAR(16)  NOT NULL DEFAULT 'ℹ️',
  `title`             VARCHAR(150) NOT NULL,
  `short_description` VARCHAR(255) DEFAULT NULL,
  `content`           MEDIUMTEXT,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`        INT          NOT NULL DEFAULT 0,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══ 012: Homepage newsletter signup ═══
CREATE TABLE IF NOT EXISTS `mc_newsletter_subscribers` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(150) NOT NULL,
  `subscribed_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Seed default page content after install with: php scripts/seed_info_pages.php
