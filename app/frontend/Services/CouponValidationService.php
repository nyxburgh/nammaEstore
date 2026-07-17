<?php
namespace App\Frontend\Services;

use App\Repositories\CouponRepository;

class CouponValidationService
{
    private CouponRepository $coupons;

    public function __construct()
    {
        $this->coupons = new CouponRepository();
    }

    /**
     * Validates a coupon code against the cart and computes the
     * discount. Does NOT record usage — that happens only after the
     * order is successfully placed (see CheckoutService).
     *
     * @param array $items Cart items — each needs product_id, vendor_id, category_id, brand_id, price/sale_price, quantity
     */
    public function validate(string $code, int $userId, float $subtotal, array $items): array
    {
        $coupon = $this->coupons->findByCode($code);
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }
        if (!$coupon['is_active']) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }
        if ($coupon['starts_at'] && strtotime($coupon['starts_at']) > time()) {
            return ['valid' => false, 'message' => 'This coupon is not active yet.'];
        }
        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }
        if ($subtotal < (float) $coupon['min_order_amount']) {
            return ['valid' => false, 'message' => 'Minimum order amount for this coupon is ₹' . number_format((float) $coupon['min_order_amount'], 2) . '.'];
        }
        if ($coupon['usage_limit_total'] !== null && (int) $coupon['used_count'] >= (int) $coupon['usage_limit_total']) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }
        if ($this->coupons->userUsageCount((int) $coupon['id'], $userId) >= (int) $coupon['usage_limit_per_user']) {
            return ['valid' => false, 'message' => 'You have already used this coupon the maximum number of times.'];
        }
        if ($coupon['scope'] === 'first_order' && $this->coupons->userHasAnyOrder($userId)) {
            return ['valid' => false, 'message' => 'This coupon is valid for first orders only.'];
        }
        if ($coupon['scope'] === 'user' && (int) $coupon['scope_id'] !== $userId) {
            return ['valid' => false, 'message' => 'This coupon is not valid for your account.'];
        }

        // ── Scope eligibility against cart contents ──────────
        $eligibleAmount = $subtotal;
        if (in_array($coupon['scope'], ['vendor', 'category', 'brand', 'product'], true)) {
            $eligibleAmount = 0.0;
            foreach ($items as $item) {
                $matches = match ($coupon['scope']) {
                    'vendor'   => (int) ($item['vendor_id'] ?? 0) === (int) $coupon['scope_id'],
                    'category' => (int) ($item['category_id'] ?? 0) === (int) $coupon['scope_id'],
                    'brand'    => (int) ($item['brand_id'] ?? 0) === (int) $coupon['scope_id'],
                    'product'  => (int) ($item['product_id'] ?? 0) === (int) $coupon['scope_id'],
                    default    => false,
                };
                if ($matches) {
                    $price = (float) ($item['sale_price'] ?: $item['price']) + (float) ($item['price_modifier'] ?? 0);
                    $eligibleAmount += $price * (int) $item['quantity'];
                }
            }
            if ($eligibleAmount <= 0) {
                return ['valid' => false, 'message' => 'Your cart doesn\'t contain any items eligible for this coupon.'];
            }
        }

        $discount = $coupon['value_type'] === 'percentage'
            ? round($eligibleAmount * (float) $coupon['value'] / 100, 2)
            : (float) $coupon['value'];

        if ($coupon['max_discount_amount'] !== null) {
            $discount = min($discount, (float) $coupon['max_discount_amount']);
        }
        $discount = min($discount, $subtotal);

        return ['valid' => true, 'coupon' => $coupon, 'discount' => $discount];
    }
}
