-- ═══════════════════════════════════════════════════════════════
-- Migration 007: OTP Verification (vendor self-registration email check)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 006_security_hardening.sql has already been imported.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this adds:
--   • A generic OTP table — used here for vendor self-registration
--     email verification, but the `purpose` column keeps it reusable
--     for other OTP flows later (spec also lists "OTP Login") without
--     a new table each time.
--
-- Business rule this implements:
--   • Admin-created vendors go live immediately (unchanged — already
--     the existing behavior).
--   • Self-registered vendors start as unverified/pending and must
--     confirm an emailed OTP before their shop goes live.
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE `mc_otps` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `identifier`  VARCHAR(150) NOT NULL COMMENT 'email or phone the OTP was sent to',
    `purpose`     VARCHAR(50) NOT NULL COMMENT 'vendor_email_verify|login|password_reset|...',
    `otp_code`    VARCHAR(10) NOT NULL,
    `attempts`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `is_used`     TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at`  DATETIME NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_lookup` (`identifier`,`purpose`,`is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
