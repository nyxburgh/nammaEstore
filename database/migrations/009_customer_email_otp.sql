-- ═══════════════════════════════════════════════════════════════
-- Migration 009: Customer email OTP (signup verification,
--                password reset, password change)
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER 007_otp_verification.sql (which created `mc_otps`).
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- What this changes:
--   • New customer signups now stay logged-out and unverified until
--     they confirm the 6-digit OTP emailed to them (same pattern as
--     vendor self-registration). Login is also blocked for accounts
--     that never verified — a fresh code is emailed on the attempt.
--   • Forgot-password and change-password now use emailed OTPs
--     (purposes 'password_reset' and 'password_change' in mc_otps).
--
-- This backfill marks every EXISTING user as verified so accounts
-- created before this feature (including admin-created customers and
-- demo/seed users) are never locked out by the new login gate.

UPDATE `mc_users`
   SET `is_verified` = 1
 WHERE `is_verified` = 0;
