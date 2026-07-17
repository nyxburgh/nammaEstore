-- ═══════════════════════════════════════════════════════════════
-- Migration 005: Shipping, Notifications, Inventory (Phase 5)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 004_gst_invoicing.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • Shipments + shipment tracking timeline, one shipment per
--     (order, vendor) pair — matches order_vendor_splits, since each
--     vendor ships their own portion of a multi-vendor order
--     independently with their own courier/tracking number
--   • In-app notifications (customer/vendor/admin) — was a fully
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
    `order_vendor_split_id` INT UNSIGNED NOT NULL,
    `vendor_id`             INT UNSIGNED NOT NULL,
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
    UNIQUE KEY `uq_split_shipment` (`order_vendor_split_id`),
    CONSTRAINT `fk_ship_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_vendor` (`vendor_id`), KEY `idx_tracking` (`tracking_number`)
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
    `user_type`   ENUM('customer','vendor','admin') NOT NULL,
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
    ('low_stock_notifications', '1', 'inventory', 'Notify vendor when stock hits threshold', 'boolean'),
    ('default_courier',         'Manual / Self-Ship', 'shipping', 'Default Courier Name', 'text')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
