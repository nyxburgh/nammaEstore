-- 012: Homepage newsletter signup — previously a no-op stub that validated
-- the email format but never persisted anything, so the same address could
-- "subscribe" endlessly. This table gives it somewhere real to write to,
-- with a unique constraint doing the dedup.
CREATE TABLE IF NOT EXISTS `mc_newsletter_subscribers` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(150) NOT NULL,
  `subscribed_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
