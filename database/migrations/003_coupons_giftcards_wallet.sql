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
--     types, and platform/vendor/category/brand/product/user/
--     first-order/festival scopes, sharing one flexible schema
--   • Gift vouchers — company/vendor/recharge types, expiry,
--     partial usage with running balance
--   • Customer wallet — balance + full audit ledger, same pattern
--     as the vendor wallet from Phase 1
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
--   scope:      platform | vendor | category | brand | product |
--               user | first_order | festival
-- scope_id holds the relevant vendor/category/brand/product/user id
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
    `scope`               ENUM('platform','vendor','category','brand','product','user','first_order','festival') NOT NULL DEFAULT 'platform',
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
    `type`             ENUM('company','vendor','recharge') NOT NULL DEFAULT 'company',
    `vendor_id`        INT UNSIGNED DEFAULT NULL COMMENT 'set only for type=vendor',
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

-- ── 4. Customer wallet (same audit-ledger pattern as vendor wallet) ──
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
