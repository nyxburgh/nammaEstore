<?php
namespace App\Frontend\Services;
use App\Core\Database;
use App\Admin\Services\CommissionService;
use App\Repositories\{CouponRepository, GiftCardRepository, CustomerWalletRepository};

class CheckoutService
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * @param string|null $couponCode    Coupon code to apply, if any.
     * @param string|null $giftCardCode  Gift card code to redeem, if any.
     * @param float       $useWalletAmount Amount the customer wants to draw from their wallet.
     */
    public function placeOrder(
        array $items, array $address, string $paymentMethod, int $userId,
        ?string $couponCode = null, ?string $giftCardCode = null, float $useWalletAmount = 0,
        int $useLoyaltyPoints = 0
    ): array {
        if (empty($items)) return ['success' => false, 'message' => 'Cart is empty.'];

        // ── Totals ───────────────────────────────────────────
        $subtotal = 0;
        foreach ($items as $item) {
            $price     = (float)($item['sale_price'] ?: $item['price'])
                       + (float)($item['price_modifier'] ?? 0);
            $subtotal += $price * (int)$item['quantity'];
        }
        $shipping = $subtotal >= 499 ? 0 : 49;

        // ── Discounts are fully validated BEFORE the order is
        // created, so the customer never gets charged before we
        // know the discount is legitimate. The actual redemption
        // (usage recording / balance debit) happens AFTER the order
        // commits, to avoid nesting a second transaction inside
        // this method's own transaction. ──────────────────────
        $couponDiscount = 0.0; $coupon = null;
        if ($couponCode) {
            $v = (new CouponValidationService())->validate($couponCode, $userId, $subtotal, $items);
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
            $couponDiscount = $v['discount'];
            $coupon = $v['coupon'];
        }

        $afterCoupon = max(0, $subtotal + $shipping - $couponDiscount);

        $giftCardApply = 0.0; $giftCard = null;
        if ($giftCardCode) {
            $v = (new GiftCardService())->validate($giftCardCode);
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
            $giftCard = $v['card'];
            $giftCardApply = min((float) $giftCard['current_balance'], $afterCoupon);
        }

        $afterGiftCard = max(0, $afterCoupon - $giftCardApply);

        $walletApply = 0.0;
        if ($useWalletAmount > 0) {
            $walletBalance = (new CustomerWalletService())->balance($userId);
            $walletApply = min($useWalletAmount, $walletBalance, $afterGiftCard);
        }

        $afterWallet = max(0, $afterGiftCard - $walletApply);

        $loyaltyService = new \App\Core\Services\LoyaltyService();
        $loyaltyApply = 0.0; $loyaltyPointsToRedeem = 0;
        if ($useLoyaltyPoints > 0) {
            $v = $loyaltyService->validateRedemption($userId, $useLoyaltyPoints);
            if (!$v['valid']) return ['success' => false, 'message' => $v['message']];
            $loyaltyApply = min($v['discount'], $afterWallet);
            // Recompute the actual points spent for the capped rupee
            // amount, so we never redeem more points than the discount
            // actually applied (e.g. order total was smaller than the
            // requested redemption).
            $loyaltyPointsToRedeem = $loyaltyApply >= $v['discount']
                ? $useLoyaltyPoints
                : $loyaltyService->rupeesToPoints($loyaltyApply);
        }

        $total = max(0, $afterWallet - $loyaltyApply);
        $orderNumber = 'BZ' . date('ymd') . strtoupper(substr(uniqid(), -5));

        $this->db->beginTransaction();
        try {
            // ── Master order ─────────────────────────────────
            $orderId = $this->db->insert(
                "INSERT INTO `".DB_PREFIX."orders`
                 (order_number, user_id, shipping_name, shipping_phone,
                  shipping_address, shipping_city, shipping_state, shipping_pincode,
                  payment_method, payment_status, order_status,
                  subtotal, shipping_charge, discount, coupon_id, coupon_code,
                  gift_card_amount, wallet_amount, loyalty_points_used, total, placed_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
                [
                    $orderNumber, $userId,
                    $address['name'],    $address['phone'],
                    $address['address'], $address['city'],
                    $address['state'],   $address['pincode'],
                    $paymentMethod, 'pending', 'placed',
                    $subtotal, $shipping, $couponDiscount,
                    $coupon['id'] ?? null, $coupon['code'] ?? null,
                    $giftCardApply, $walletApply, $loyaltyPointsToRedeem, $total,
                ]
            );

            // ── Group by seller ──────────────────────────────
            $sellerGroups = [];
            foreach ($items as $item) {
                $vid = (int)($item['seller_id']
                       ?? $this->getProductSeller((int)$item['product_id']));
                $sellerGroups[$vid][] = $item;
            }

            $commSvc    = new CommissionService();

            foreach ($sellerGroups as $sellerId => $vItems) {
                $grossSeller = 0;
                $itemIds     = [];

                // Insert order items and collect IDs
                foreach ($vItems as $item) {
                    $unitPrice    = (float)($item['sale_price'] ?: $item['price'])
                                  + (float)($item['price_modifier'] ?? 0);
                    $sub          = round($unitPrice * (int)$item['quantity'], 2);
                    $grossSeller += $sub;

                    $variantLabel = !empty($item['variant_value'])
                        ? (($item['variant_name'] ?? 'Option') . ': ' . $item['variant_value'])
                        : null;

                    $itemId = $this->db->insert(
                        "INSERT INTO `".DB_PREFIX."order_items`
                         (order_id, product_id, seller_id, variant_id,
                          product_name, variant_label, quantity, unit_price, subtotal, status)
                         VALUES (?,?,?,?,?,?,?,?,?,'placed')",
                        [
                            $orderId, (int)$item['product_id'], $sellerId,
                            $item['variant_id'] ?? null,
                            $item['name'], $variantLabel,
                            (int)$item['quantity'], $unitPrice, $sub,
                        ]
                    );
                    $itemIds[] = ['id' => $itemId, 'item' => $item,
                                  'unitPrice' => $unitPrice, 'sub' => $sub];

                    // Decrement stock (floor at 0)
                    $this->db->execute(
                        "UPDATE `".DB_PREFIX."products`
                         SET stock = GREATEST(0, stock - ?) WHERE id = ?",
                        [(int)$item['quantity'], (int)$item['product_id']]
                    );
                    $this->checkLowStock((int)$item['product_id'], $sellerId, (int)$item['quantity']);
                }

                // ── Seller commission split ───────────────────
                $comm = $commSvc->calculate($sellerId, $grossSeller);
                $this->db->insert(
                    "INSERT INTO `".DB_PREFIX."order_seller_splits`
                     (order_id, seller_id, gross_amount, commission_pct,
                      commission_amount, seller_earning, payout_status)
                     VALUES (?,?,?,?,?,?,'pending')",
                    [
                        $orderId, $sellerId, $grossSeller,
                        $comm['commission_pct'], $comm['commission_amount'],
                        $comm['seller_earning'],
                    ]
                );
                $commSvc->addToTurnover($sellerId, $grossSeller);

                (new \App\Core\Services\NotificationService())->notify(
                    'seller', $sellerId, 'order_placed',
                    'New order received',
                    "Order #{$orderNumber} — " . currency($grossSeller) . " (" . count($vItems) . " item" . (count($vItems) > 1 ? 's' : '') . ")",
                    SELLER_URL . '/orders'
                );

                // ── Per-item commission log ────────────────────
                foreach ($itemIds as $entry) {
                    $ic = $commSvc->calculate($sellerId, $entry['sub']);
                    $this->db->insert(
                        "INSERT INTO `".DB_PREFIX."commissions`
                         (order_id, order_item_id, seller_id, product_id,
                          plan_id, plan_name, item_amount, turnover_before,
                          commission_pct, commission_amount, seller_earning)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                        [
                            $orderId, $entry['id'], $sellerId,
                            (int)$entry['item']['product_id'],
                            $ic['plan_id'], $ic['plan_name'], $entry['sub'],
                            $ic['turnover_before'], $ic['commission_pct'],
                            $ic['commission_amount'], $ic['seller_earning'],
                        ]
                    );
                }
            }

            // ── Status timeline ───────────────────────────────
            $this->db->insert(
                "INSERT INTO `".DB_PREFIX."order_status_timeline`
                 (order_id, status, note, changed_by_type)
                 VALUES (?,'placed','Order placed by customer','system')",
                [$orderId]
            );

            // ── Payment record (non-COD) ──────────────────────
            if ($paymentMethod !== 'cod') {
                $this->db->insert(
                    "INSERT INTO `".DB_PREFIX."payments`
                     (order_id, user_id, amount, status)
                     VALUES (?,?,?,'pending')",
                    [$orderId, $userId, $total]
                );
            }

            $this->db->commit();

            // ── Post-commit: redeem coupon/gift-card/wallet ───
            // Each of these manages its own transaction — kept
            // outside the block above to avoid nesting a second
            // transaction inside this method's. All three were
            // already validated before the order was created, so
            // failure here is exceptional rather than expected.
            try {
                if ($coupon) {
                    (new CouponRepository())->recordUsage((int) $coupon['id'], $userId, $orderId, $couponDiscount);
                }
                if ($giftCard && $giftCardApply > 0) {
                    (new GiftCardRepository())->redeem((int) $giftCard['id'], $giftCardApply, $orderId);
                }
                if ($walletApply > 0) {
                    (new CustomerWalletRepository())->debit($userId, $walletApply, 'order_payment', $orderId, "Payment for order #{$orderNumber}");
                }
                if ($loyaltyPointsToRedeem > 0) {
                    $loyaltyService->redeem($userId, $loyaltyPointsToRedeem, $orderId);
                }
                (new \App\Admin\Services\InvoiceService())->generateForOrder($orderId);
            } catch (\Exception $e) {
                // Order already exists — log rather than fail the
                // whole checkout at this point.
                error_log('Post-order discount redemption failed for order ' . $orderId . ': ' . $e->getMessage());
            }

            return [
                'success'      => true,
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
                'total'        => $total,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Order failed: ' . $e->getMessage()];
        }
    }

    private function getProductSeller(int $productId): int
    {
        return (int)($this->db->fetchOne(
            "SELECT seller_id FROM `".DB_PREFIX."products` WHERE id = ?",
            [$productId]
        )['seller_id'] ?? 1);
    }

    /**
     * Notifies the seller once when a product's stock crosses at or
     * below its low_stock_threshold. Only fires on the order that
     * caused the crossing (stock was above threshold before this
     * order's decrement, at or below it after) — not on every
     * subsequent sale once already low.
     */
    private function checkLowStock(int $productId, int $sellerId, int $qtyJustDecremented): void
    {
        if ((new \App\Repositories\SettingsRepository())->get('low_stock_notifications', '1') !== '1') {
            return;
        }
        $p = $this->db->fetchOne(
            "SELECT name, stock, low_stock_threshold FROM `".DB_PREFIX."products` WHERE id = ?",
            [$productId]
        );
        if (!$p) return;

        $stockBefore = (int) $p['stock'] + $qtyJustDecremented;
        $justCrossed = $stockBefore > (int) $p['low_stock_threshold'] && (int) $p['stock'] <= (int) $p['low_stock_threshold'];
        if (!$justCrossed) return;

        $level = (int) $p['stock'] === 0 ? 'Out of stock' : 'Low stock';
        (new \App\Core\Services\NotificationService())->notify(
            'seller', $sellerId, 'low_stock',
            "{$level}: {$p['name']}",
            "Only {$p['stock']} unit(s) left.",
            SELLER_URL . '/products'
        );
    }
}
