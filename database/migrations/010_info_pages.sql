-- 010: Info Center pages become admin-manageable.
-- Content supports tokens replaced at render time:
--   {{app_url}} {{vendor_url}} {{site_name}} {{site_email}} {{site_phone}}
CREATE TABLE IF NOT EXISTS `mc_pages` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`              VARCHAR(100) NOT NULL,
  `icon`              VARCHAR(16)  NOT NULL DEFAULT 'ℹ️',
  `title`             VARCHAR(150) NOT NULL,
  `short_description` VARCHAR(255) DEFAULT NULL,
  `content`           MEDIUMTEXT,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`        INT          NOT NULL DEFAULT 0,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
