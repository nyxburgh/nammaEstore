-- ═══════════════════════════════════════════════════════════════
-- Migration 004: GST & Invoicing (Phase 4)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 003_coupons_giftcards_wallet.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • HSN code + GST rate on products (required to itemize tax on
--     any invoice — was missing entirely)
--   • Vendor registered address/state (required for place-of-supply:
--     CGST+SGST for intra-state sales, IGST for inter-state — this
--     was informally patched around in Phase 1's vendor settings
--     with a silent try/catch; this migration makes those columns
--     real so GST calculation has something reliable to read)
--   • Company/platform GST identity (for the invoice letterhead)
--   • Invoices + invoice line items, one invoice per vendor's
--     portion of an order (matches how Indian marketplaces actually
--     invoice — each seller is the seller-of-record for GST purposes)
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Product tax fields ─────────────────────────────────────
ALTER TABLE `mc_products`
    ADD COLUMN `hsn_code` VARCHAR(20) DEFAULT NULL AFTER `sku`,
    ADD COLUMN `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 18.00 AFTER `hsn_code`;

-- ── 2. Vendor registered address (place of supply) ────────────
-- Only added if not already present — Phase 1's VendorSettingsService
-- wrote to these defensively via try/catch in case a prior manual
-- migration had already added them; this is the authoritative version.
ALTER TABLE `mc_vendor_profiles`
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
-- One invoice per (order, vendor) pair — mirrors order_vendor_splits,
-- since each vendor is the seller-of-record for their portion of a
-- multi-vendor order and GST law requires the invoice to name the
-- actual seller, not the platform.
CREATE TABLE `mc_invoices` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number`        VARCHAR(50) NOT NULL UNIQUE,
    `order_id`              INT UNSIGNED NOT NULL,
    `order_vendor_split_id` INT UNSIGNED NOT NULL,
    `vendor_id`             INT UNSIGNED NOT NULL,
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
    UNIQUE KEY `uq_split_invoice` (`order_vendor_split_id`),
    CONSTRAINT `fk_inv_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_vendor` (`vendor_id`), KEY `idx_user` (`user_id`)
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
