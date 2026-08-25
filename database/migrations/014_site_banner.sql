-- ═══════════════════════════════════════════════════════════════
-- Migration 014: Site-wide floating banner (dev/test/maintenance notice)
-- ═══════════════════════════════════════════════════════════════
-- Lets Admin → Settings toggle a floating storefront notice on/off and
-- edit its text without a code deploy — e.g. "site under development"
-- during testing, later re-purposed as a "site under maintenance" notice.
INSERT INTO `mc_settings` (`key`,`value`,`group`,`label`,`type`) VALUES
('site_banner_enabled', '1', 'general', 'Show Floating Site Notice', 'boolean'),
('site_banner_message', 'This site is under development and is currently a test version.', 'general', 'Floating Site Notice Text', 'text')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
