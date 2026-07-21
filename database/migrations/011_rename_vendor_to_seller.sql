-- ═══════════════════════════════════════════════════════════════
-- Migration 011: Rename "vendor" to "seller" throughout the schema
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 010_info_pages.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- The app previously called sellers "vendors", which was confusing
-- against "user" (buyer). This migration renames every vendor_*
-- table/column, vendor-related ENUM/data value, FK, and index to
-- seller_* so the DB matches the app-wide seller terminology.
--
-- Each ENUM column is widened to accept both 'vendor' and 'seller'
-- before the data UPDATE, then narrowed back down — this avoids any
-- data loss/truncation regardless of the server's sql_mode.
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Rename tables ──────────────────────────────────────────
RENAME TABLE
    `mc_vendor_profiles`          TO `mc_seller_profiles`,
    `mc_vendor_subscriptions`     TO `mc_seller_subscriptions`,
    `mc_vendor_monthly_turnover`  TO `mc_seller_monthly_turnover`,
    `mc_vendor_wallets`           TO `mc_seller_wallets`,
    `mc_vendor_wallet_transactions` TO `mc_seller_wallet_transactions`,
    `mc_vendor_settlements`       TO `mc_seller_settlements`,
    `mc_vendor_withdrawals`       TO `mc_seller_withdrawals`,
    `mc_vendor_documents`         TO `mc_seller_documents`,
    `mc_order_vendor_splits`      TO `mc_order_seller_splits`;

-- ── 2. Rename FK constraints containing "vendor" ─────────────
ALTER TABLE `mc_products`  DROP FOREIGN KEY `fk_prod_vendor`;
ALTER TABLE `mc_seller_monthly_turnover` DROP FOREIGN KEY `fk_vmt_vendor`;
ALTER TABLE `mc_seller_subscriptions`    DROP FOREIGN KEY `fk_vs_vendor`;
ALTER TABLE `mc_seller_wallets`          DROP FOREIGN KEY `fk_vw_vendor`;

-- ── 3. Rename vendor_id / vendor_type / vendor_earning / order_vendor_split_id columns ──
ALTER TABLE `mc_commissions`             CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL,
                                          CHANGE COLUMN `vendor_earning` `seller_earning` DECIMAL(12,2) NOT NULL;
ALTER TABLE `mc_disputes`                CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_gift_cards`              CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED DEFAULT NULL COMMENT 'set only for type=seller';
ALTER TABLE `mc_invoices`                CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL,
                                          CHANGE COLUMN `order_vendor_split_id` `order_seller_split_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_order_items`             CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_order_seller_splits`     CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL,
                                          CHANGE COLUMN `vendor_earning` `seller_earning` DECIMAL(12,2) NOT NULL;
ALTER TABLE `mc_products`                CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_returns`                 CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_shipments`               CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL,
                                          CHANGE COLUMN `order_vendor_split_id` `order_seller_split_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_documents`        CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_monthly_turnover` CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_settlements`      CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL,
                                          CHANGE COLUMN `order_vendor_split_id` `order_seller_split_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_subscriptions`    CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_wallets`          CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_wallet_transactions` CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_withdrawals`      CHANGE COLUMN `vendor_id` `seller_id` INT UNSIGNED NOT NULL;
ALTER TABLE `mc_seller_profiles`         CHANGE COLUMN `vendor_type` `seller_type` ENUM('subscription','commission') NOT NULL DEFAULT 'commission';

-- ── 4. Recreate FK constraints under seller_* names ───────────
ALTER TABLE `mc_products`  ADD CONSTRAINT `fk_prod_seller` FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE;
ALTER TABLE `mc_seller_monthly_turnover` ADD CONSTRAINT `fk_smt_seller` FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE;
ALTER TABLE `mc_seller_subscriptions`    ADD CONSTRAINT `fk_ss_seller`  FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE;
ALTER TABLE `mc_seller_wallets`          ADD CONSTRAINT `fk_sw_seller`  FOREIGN KEY (`seller_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE;

-- ── 5. Rename indexes containing "vendor" ─────────────────────
-- MariaDB 10.4 (this server) doesn't support ALTER TABLE ... RENAME
-- INDEX (added in 10.5.2) — use DROP INDEX + ADD INDEX instead.
ALTER TABLE `mc_commissions`             DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_disputes`                DROP INDEX `idx_vendor_status`, ADD INDEX `idx_seller_status` (`seller_id`,`status`);
ALTER TABLE `mc_invoices`                DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_order_items`             DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_order_seller_splits`     DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_order_seller_splits`     DROP INDEX `uq_order_vendor`, ADD UNIQUE KEY `uq_order_seller` (`order_id`,`seller_id`);
ALTER TABLE `mc_products`                DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_returns`                 DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_shipments`               DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_seller_documents`        DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_seller_monthly_turnover` DROP INDEX `uq_vendor_month_year`, ADD UNIQUE KEY `uq_seller_month_year` (`seller_id`,`month`,`year`);
ALTER TABLE `mc_seller_settlements`      DROP INDEX `idx_vendor_status`, ADD INDEX `idx_seller_status` (`seller_id`,`status`);
ALTER TABLE `mc_seller_subscriptions`    DROP INDEX `idx_vendor_status`, ADD INDEX `idx_seller_status` (`seller_id`,`status`);
ALTER TABLE `mc_seller_wallets`          DROP INDEX `vendor_id`, ADD UNIQUE KEY `seller_id` (`seller_id`);
ALTER TABLE `mc_seller_wallet_transactions` DROP INDEX `idx_vendor`, ADD INDEX `idx_seller` (`seller_id`);
ALTER TABLE `mc_seller_withdrawals`      DROP INDEX `idx_vendor_status`, ADD INDEX `idx_seller_status` (`seller_id`,`status`);

-- ── 6. ENUM / data values: widen, backfill, narrow ────────────
ALTER TABLE `mc_users` MODIFY COLUMN `role` ENUM('customer','vendor','seller') NOT NULL DEFAULT 'customer';
UPDATE `mc_users` SET `role` = 'seller' WHERE `role` = 'vendor';
ALTER TABLE `mc_users` MODIFY COLUMN `role` ENUM('customer','seller') NOT NULL DEFAULT 'customer';

ALTER TABLE `mc_activity_logs` MODIFY COLUMN `actor_type` ENUM('admin','sub_admin','vendor','customer','seller') NOT NULL;
UPDATE `mc_activity_logs` SET `actor_type` = 'seller' WHERE `actor_type` = 'vendor';
ALTER TABLE `mc_activity_logs` MODIFY COLUMN `actor_type` ENUM('admin','sub_admin','customer','seller') NOT NULL;

ALTER TABLE `mc_disputes` MODIFY COLUMN `raised_by` ENUM('customer','vendor','admin','seller') NOT NULL;
UPDATE `mc_disputes` SET `raised_by` = 'seller' WHERE `raised_by` = 'vendor';
ALTER TABLE `mc_disputes` MODIFY COLUMN `raised_by` ENUM('customer','admin','seller') NOT NULL;

ALTER TABLE `mc_order_status_timeline` MODIFY COLUMN `changed_by_type` ENUM('admin','vendor','system','seller') NOT NULL DEFAULT 'system';
UPDATE `mc_order_status_timeline` SET `changed_by_type` = 'seller' WHERE `changed_by_type` = 'vendor';
ALTER TABLE `mc_order_status_timeline` MODIFY COLUMN `changed_by_type` ENUM('admin','system','seller') NOT NULL DEFAULT 'system';

ALTER TABLE `mc_notifications` MODIFY COLUMN `user_type` ENUM('customer','vendor','admin','seller') NOT NULL;
UPDATE `mc_notifications` SET `user_type` = 'seller' WHERE `user_type` = 'vendor';
ALTER TABLE `mc_notifications` MODIFY COLUMN `user_type` ENUM('customer','admin','seller') NOT NULL;

ALTER TABLE `mc_gift_cards` MODIFY COLUMN `type` ENUM('company','vendor','recharge','seller') NOT NULL DEFAULT 'company';
UPDATE `mc_gift_cards` SET `type` = 'seller' WHERE `type` = 'vendor';
ALTER TABLE `mc_gift_cards` MODIFY COLUMN `type` ENUM('company','recharge','seller') NOT NULL DEFAULT 'company';

ALTER TABLE `mc_coupons` MODIFY COLUMN `scope` ENUM('platform','vendor','category','brand','product','user','first_order','festival','seller') NOT NULL DEFAULT 'platform';
UPDATE `mc_coupons` SET `scope` = 'seller' WHERE `scope` = 'vendor';
ALTER TABLE `mc_coupons` MODIFY COLUMN `scope` ENUM('platform','category','brand','product','user','first_order','festival','seller') NOT NULL DEFAULT 'platform';

UPDATE `mc_otps` SET `purpose` = 'seller_email_verify' WHERE `purpose` = 'vendor_email_verify';

-- ── 7. Misc text referencing "vendor" in settings ─────────────
UPDATE `mc_settings` SET `label` = 'Notify seller when stock hits threshold' WHERE `key` = 'low_stock_notifications';
