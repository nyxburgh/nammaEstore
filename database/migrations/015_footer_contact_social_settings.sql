-- ═══════════════════════════════════════════════════════════════
-- Migration 015: Footer contact & social settings
-- ═══════════════════════════════════════════════════════════════
-- Run AFTER full_install.sql / previous migrations.
-- phpMyAdmin: select mycart_marketplace → SQL tab → paste → Go.
--
-- Adds the settings keys the storefront footer now reads for the
-- address line and social media links (Admin → Settings → General).
-- site_email / site_phone already exist from the base install.

INSERT IGNORE INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
('site_address',     '', 'general', 'Store Address',     'text'),
('social_facebook',  '', 'general', 'Facebook URL',      'text'),
('social_instagram', '', 'general', 'Instagram URL',     'text'),
('social_twitter',   '', 'general', 'Twitter / X URL',   'text'),
('social_youtube',   '', 'general', 'YouTube URL',       'text');
