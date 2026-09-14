-- ═══════════════════════════════════════════════════════════════
-- Migration 018: Back-in-stock notifications ("Remind Me Later")
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER full_install.sql / previous migrations.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- A logged-in customer viewing an out-of-stock product can ask to be
-- notified once it's back — one row per (user, product); notified_at
-- is set the first time a restock notification actually goes out, so
-- the same request isn't fired twice.

CREATE TABLE IF NOT EXISTS `mc_stock_notifications` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED NOT NULL,
  `notified_at` DATETIME NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `fk_stocknotif_user` FOREIGN KEY (`user_id`) REFERENCES `mc_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stocknotif_product` FOREIGN KEY (`product_id`) REFERENCES `mc_products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
