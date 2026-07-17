-- ============================================================
-- MY CART — Multi-Vendor Marketplace
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
  `actor_type`  ENUM('admin','sub_admin','vendor','customer') NOT NULL,
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
  `role`              ENUM('customer','vendor') NOT NULL DEFAULT 'customer',
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

-- 3. VENDOR PROFILES & SUBSCRIPTIONS
CREATE TABLE `mc_vendor_profiles` (
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

CREATE TABLE `mc_vendor_subscriptions` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id`   INT UNSIGNED NOT NULL,
  `plan_id`     INT UNSIGNED NOT NULL,
  `status`      ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `started_at`  DATETIME NOT NULL,
  `expires_at`  DATETIME DEFAULT NULL,
  `payment_ref` VARCHAR(100) DEFAULT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vs_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vs_plan` FOREIGN KEY (`plan_id`) REFERENCES `mc_subscription_plans`(`id`),
  KEY `idx_vendor_status` (`vendor_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_vendor_monthly_turnover` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id`      INT UNSIGNED NOT NULL,
  `month`          TINYINT UNSIGNED NOT NULL,
  `year`           SMALLINT UNSIGNED NOT NULL,
  `total_turnover` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `total_orders`   INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_vendor_month_year` (`vendor_id`,`month`,`year`),
  CONSTRAINT `fk_vmt_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
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
  `vendor_id`           INT UNSIGNED NOT NULL,
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
  CONSTRAINT `fk_prod_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `mc_categories`(`id`) ON DELETE SET NULL,
  KEY `idx_vendor` (`vendor_id`), KEY `idx_status` (`status`)
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
  `vendor_id`     INT UNSIGNED NOT NULL,
  `variant_id`    INT UNSIGNED DEFAULT NULL,
  `product_name`  VARCHAR(255) NOT NULL,
  `variant_label` VARCHAR(100) DEFAULT NULL,
  `quantity`      INT NOT NULL,
  `unit_price`    DECIMAL(12,2) NOT NULL,
  `subtotal`      DECIMAL(14,2) NOT NULL,
  `status`        ENUM('placed','confirmed','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'placed',
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
  KEY `idx_order` (`order_id`), KEY `idx_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_order_vendor_splits` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`          INT UNSIGNED NOT NULL,
  `vendor_id`         INT UNSIGNED NOT NULL,
  `gross_amount`      DECIMAL(14,2) NOT NULL,
  `commission_pct`    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `commission_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `vendor_earning`    DECIMAL(12,2) NOT NULL,
  `payout_status`     ENUM('pending','processing','paid','on_hold') NOT NULL DEFAULT 'pending',
  `payout_date`       DATETIME DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_order_vendor` (`order_id`,`vendor_id`),
  CONSTRAINT `fk_ovs_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
  KEY `idx_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mc_order_status_timeline` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`        INT UNSIGNED NOT NULL,
  `status`          VARCHAR(50) NOT NULL,
  `note`            TEXT DEFAULT NULL,
  `changed_by_type` ENUM('admin','vendor','system') NOT NULL DEFAULT 'system',
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
  `vendor_id`         INT UNSIGNED NOT NULL,
  `product_id`        INT UNSIGNED NOT NULL,
  `plan_id`           INT UNSIGNED DEFAULT NULL,
  `plan_name`         VARCHAR(100) NOT NULL,
  `item_amount`       DECIMAL(12,2) NOT NULL,
  `turnover_before`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `commission_pct`    DECIMAL(5,2) NOT NULL,
  `commission_amount` DECIMAL(12,2) NOT NULL,
  `vendor_earning`    DECIMAL(12,2) NOT NULL,
  `calculated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_vendor` (`vendor_id`), KEY `idx_order` (`order_id`), KEY `idx_calc` (`calculated_at`)
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

-- ─── Vendor profile extensions ──────────────────────────────
ALTER TABLE `mc_vendor_profiles`
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
