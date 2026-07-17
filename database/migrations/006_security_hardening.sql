-- ═══════════════════════════════════════════════════════════════
-- Migration 006: Security Hardening (Phase 6)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 005_shipping_notifications_inventory.sql has already
-- been imported. phpMyAdmin: select mycart_marketplace → SQL tab →
-- paste → Go.
--
-- What this adds:
--   • Login attempt lockout for both customer/vendor and admin login
--   • A generic rate-limit table (login throttling, and reusable for
--     any other sensitive endpoint)
--   • Remember-me token support actually wired up — the column
--     existed on mc_users since the original build but no code ever
--     used it
--   • Fixes mc_admin_permissions.module — its ENUM only listed the
--     5 original modules and was never widened when Phases 1 and 3
--     added settlements/returns/disputes/coupons/gift_cards/brands,
--     so granting a sub-admin permission on any of those would have
--     failed silently
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Login lockout ──────────────────────────────────────────
ALTER TABLE `mc_users`
    ADD COLUMN `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `last_login`,
    ADD COLUMN `locked_until` DATETIME DEFAULT NULL AFTER `failed_login_attempts`;

ALTER TABLE `mc_admins`
    ADD COLUMN `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `last_login`,
    ADD COLUMN `locked_until` DATETIME DEFAULT NULL AFTER `failed_login_attempts`,
    ADD COLUMN `remember_token` VARCHAR(100) DEFAULT NULL AFTER `locked_until`;

-- ── 2. Generic rate limiter ───────────────────────────────────
-- Used for login throttling by IP+identifier; reusable for any
-- other endpoint worth rate-limiting later (OTP requests, coupon
-- brute-forcing, etc.) without a new table each time.
CREATE TABLE `mc_rate_limits` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rate_key`      VARCHAR(190) NOT NULL UNIQUE,
    `attempts`      INT UNSIGNED NOT NULL DEFAULT 1,
    `first_attempt_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `blocked_until` DATETIME DEFAULT NULL,
    KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Fix admin_permissions module ENUM ──────────────────────
ALTER TABLE `mc_admin_permissions`
    MODIFY COLUMN `module` ENUM(
        'products','payments','reports','activity','banners',
        'settlements','returns','disputes','coupons','gift_cards','brands'
    ) NOT NULL;
