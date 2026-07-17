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
