-- 013: The homepage flash ticker advertises "code NAMMA15" but mc_coupons had
-- no seed data at all, so the advertised code always failed validation with
-- "Invalid coupon code." Seed the coupon it actually promises, plus a
-- matching gift voucher code for the "gift voucher" copy alongside it, so
-- what customers see on the homepage is redeemable at checkout.
INSERT INTO `mc_coupons`
  (`code`, `label`, `value_type`, `value`, `max_discount_amount`, `min_order_amount`, `scope`, `usage_limit_total`, `usage_limit_per_user`, `is_active`)
SELECT * FROM (SELECT
  'NAMMA15' AS code, 'Flash Sale — 15% off' AS label, 'percentage' AS value_type, 15.00 AS value,
  500.00 AS max_discount_amount, 0.00 AS min_order_amount, 'platform' AS scope,
  NULL AS usage_limit_total, 1 AS usage_limit_per_user, 1 AS is_active
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mc_coupons` WHERE `code` = 'NAMMA15');

INSERT INTO `mc_gift_cards`
  (`code`, `type`, `initial_balance`, `current_balance`, `status`)
SELECT * FROM (SELECT
  'WELCOME100' AS code, 'company' AS type, 100.00 AS initial_balance, 100.00 AS current_balance, 'active' AS status
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `mc_gift_cards` WHERE `code` = 'WELCOME100');
