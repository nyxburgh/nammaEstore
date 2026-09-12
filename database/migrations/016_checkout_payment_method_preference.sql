-- ═══════════════════════════════════════════════════════════════
-- Migration 016: Checkout payment sub-method preference
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER full_install.sql / previous migrations.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- The checkout page now shows separate cards for UPI / Card /
-- Net Banking / Wallet (all still route through the same Razorpay
-- "online" flow — mc_orders.payment_method stays 'cod' or 'online').
-- This column just remembers which card the customer picked, so the
-- Razorpay Checkout.js modal on /payment/{id} can open directly on
-- that method instead of showing every tab.

ALTER TABLE `mc_orders`
  ADD COLUMN IF NOT EXISTS `preferred_method` VARCHAR(20) NULL AFTER `payment_method`;
