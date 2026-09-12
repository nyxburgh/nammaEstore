-- ═══════════════════════════════════════════════════════════════
-- Migration 017: Seller approval step for returns/replacements
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER full_install.sql / previous migrations.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- New flow: customer requests a return ('requested') → the SELLER
-- approves or rejects it first → only once seller-approved can an
-- admin process the actual refund ('refunded'). mc_returns.status
-- already has the right enum values (requested/approved/rejected/
-- refunded) — this just adds separate seller-action columns so the
-- seller's approve/reject doesn't overwrite the existing admin
-- resolved_at/resolved_by columns when admin later processes the
-- refund.

ALTER TABLE `mc_returns`
  ADD COLUMN IF NOT EXISTS `seller_resolved_at` DATETIME NULL AFTER `seller_id`,
  ADD COLUMN IF NOT EXISTS `seller_resolved_by` INT UNSIGNED NULL AFTER `seller_resolved_at`;
