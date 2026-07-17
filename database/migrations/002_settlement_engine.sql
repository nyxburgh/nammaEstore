-- ═══════════════════════════════════════════════════════════════
-- Migration 002: Vendor Settlement Engine (Phase 1)
-- ═══════════════════════════════════════════════════════════════
-- Run this AFTER the base database.sql has already been imported.
-- In phpMyAdmin (XAMPP): select mycart_marketplace → SQL tab →
-- paste this file's contents → Go.
--
-- What this adds:
--   • Explicit subscription-vs-commission vendor split (was a
--     turnover-threshold heuristic before — now matches the spec's
--     two genuinely separate settlement paths)
--   • Return window tracking (orders.delivered_at)
--   • Returns/replacements/cancellations (a hard dependency of
--     settlement — money can't be released until the return window
--     is provably closed)
--   • Disputes (settlement is blocked while a dispute is open)
--   • Vendor wallet + a full audit-ledger of every balance change
--   • Vendor settlements (the per-order deduction waterfall)
--   • Vendor withdrawals (payout requests: UPI/Bank/Wallet,
--     manual or auto approval)
--   • Vendor KYC documents (GST cert, PAN, cancelled cheque, etc.)
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Explicit vendor type ──────────────────────────────────
-- 'subscription' = pays a plan fee, 0% commission while active,
--                   settlement BLOCKED entirely if subscription lapses.
-- 'commission'   = no subscription, a % is deducted from every order,
--                   settlement is never blocked by subscription status.
ALTER TABLE `mc_vendor_profiles`
    ADD COLUMN `vendor_type` ENUM('subscription','commission') NOT NULL DEFAULT 'commission' AFTER `status`;

-- ── 2. Delivery timestamp (return-window anchor) ─────────────
ALTER TABLE `mc_orders`
    ADD COLUMN `delivered_at` DATETIME DEFAULT NULL AFTER `placed_at`;

-- ── 3. Returns / Replacements / Cancellations ────────────────
CREATE TABLE `mc_returns` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`       INT UNSIGNED NOT NULL,
    `order_item_id`  INT UNSIGNED NOT NULL,
    `user_id`        INT UNSIGNED NOT NULL,
    `vendor_id`      INT UNSIGNED NOT NULL,
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
    KEY `idx_vendor` (`vendor_id`), KEY `idx_status` (`status`), KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Disputes ───────────────────────────────────────────────
-- A minimal flag-level table for Phase 0/1 — settlement checks
-- "no open dispute" against this. Full dispute workflow (messages,
-- evidence, admin mediation) is a later phase.
CREATE TABLE `mc_disputes` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`    INT UNSIGNED NOT NULL,
    `vendor_id`   INT UNSIGNED NOT NULL,
    `raised_by`   ENUM('customer','vendor','admin') NOT NULL,
    `raised_by_id` INT UNSIGNED NOT NULL,
    `reason`      VARCHAR(255) NOT NULL,
    `status`      ENUM('open','resolved') NOT NULL DEFAULT 'open',
    `resolution_note` TEXT DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_disp_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_vendor_status` (`vendor_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. Vendor wallet ──────────────────────────────────────────
CREATE TABLE `mc_vendor_wallets` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vendor_id`        INT UNSIGNED NOT NULL UNIQUE,
    `balance`          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_earned`     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `total_withdrawn`  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_vw_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Full audit trail of every balance change — required for any
-- financial ledger, and for resolving vendor payout disputes.
CREATE TABLE `mc_vendor_wallet_transactions` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vendor_id`        INT UNSIGNED NOT NULL,
    `type`             ENUM('credit','debit') NOT NULL,
    `amount`           DECIMAL(12,2) NOT NULL,
    `balance_after`    DECIMAL(14,2) NOT NULL,
    `reference_type`   ENUM('settlement','withdrawal','penalty','refund_deduction','adjustment') NOT NULL,
    `reference_id`     INT UNSIGNED DEFAULT NULL,
    `description`      VARCHAR(255) DEFAULT NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_vendor` (`vendor_id`), KEY `idx_reference` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. Vendor settlements ─────────────────────────────────────
-- One row per order_vendor_split that has become (or was evaluated
-- to become) eligible. Holds the full deduction waterfall from the
-- spec: Order Amount − Tax − Shipping − Platform Charges −
-- Commission − Penalty − Refund − Subscription Due = Vendor Payable.
CREATE TABLE `mc_vendor_settlements` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vendor_id`            INT UNSIGNED NOT NULL,
    `order_id`             INT UNSIGNED NOT NULL,
    `order_vendor_split_id` INT UNSIGNED NOT NULL,
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
    UNIQUE KEY `uq_split` (`order_vendor_split_id`),
    CONSTRAINT `fk_settle_order` FOREIGN KEY (`order_id`) REFERENCES `mc_orders`(`id`) ON DELETE CASCADE,
    KEY `idx_vendor_status` (`vendor_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 7. Vendor withdrawals (payout requests) ──────────────────
CREATE TABLE `mc_vendor_withdrawals` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vendor_id`       INT UNSIGNED NOT NULL,
    `amount`          DECIMAL(12,2) NOT NULL,
    `method`          ENUM('upi','bank_transfer','wallet') NOT NULL,
    `method_details`  VARCHAR(255) DEFAULT NULL,
    `status`          ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
    `admin_note`      VARCHAR(255) DEFAULT NULL,
    `requested_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at`    DATETIME DEFAULT NULL,
    `processed_by`    INT UNSIGNED DEFAULT NULL,
    KEY `idx_vendor_status` (`vendor_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 8. Vendor KYC documents ───────────────────────────────────
CREATE TABLE `mc_vendor_documents` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `vendor_id`    INT UNSIGNED NOT NULL,
    `doc_type`     ENUM('gst_certificate','pan_card','cancelled_cheque','shop_license','other') NOT NULL,
    `file_path`    VARCHAR(255) NOT NULL,
    `status`       ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `uploaded_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `verified_by`  INT UNSIGNED DEFAULT NULL,
    `verified_at`  DATETIME DEFAULT NULL,
    KEY `idx_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 9. Settlement-related config (key/value in existing settings table) ──
INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
    ('settlement_min_withdrawal',      '1000', 'settlement', 'Minimum Withdrawal Amount (₹)',    'number'),
    ('settlement_return_window_days',  '7',    'settlement', 'Return Window (days after delivery)', 'number'),
    ('settlement_auto_approve',        '0',    'settlement', 'Auto-approve withdrawals under threshold', 'boolean'),
    ('settlement_platform_charge_pct', '0',    'settlement', 'Flat Platform Charge (%)',          'number')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ── 10. Backfill existing vendors ────────────────────────────
-- Anyone currently on a paid plan is treated as a subscription
-- vendor going forward; everyone else defaults to commission
-- (matches the ALTER TABLE default, this just makes it explicit
-- for vendors that already have an active paid subscription).
UPDATE `mc_vendor_profiles` vp
JOIN `mc_vendor_subscriptions` vs ON vs.vendor_id = vp.user_id AND vs.status = 'active'
JOIN `mc_subscription_plans` sp ON sp.id = vs.plan_id AND sp.price > 0
SET vp.vendor_type = 'subscription';

-- Give every existing vendor a wallet row so lookups never need
-- an existence check.
INSERT INTO `mc_vendor_wallets` (vendor_id)
SELECT id FROM `mc_users` WHERE role = 'vendor'
ON DUPLICATE KEY UPDATE vendor_id = vendor_id;
