<?php
namespace App\Core\Services;

use App\Repositories\{LoyaltyRepository, SettingsRepository};

class LoyaltyService
{
    private LoyaltyRepository $loyalty;
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->loyalty  = new LoyaltyRepository();
        $this->settings = new SettingsRepository();
    }

    public function isEnabled(): bool
    {
        return $this->settings->get('loyalty_enabled', '1') === '1';
    }

    public function balance(int $userId): int
    {
        return (int) $this->loyalty->getOrCreate($userId)['balance'];
    }

    public function overview(int $userId): array
    {
        return $this->loyalty->getOrCreate($userId);
    }

    public function transactions(int $userId, int $page): array
    {
        return $this->loyalty->getTransactions($userId, $page);
    }

    /** Rupee value of a given number of points, at the current redeem rate. */
    public function pointsToRupees(int $points): float
    {
        return round($points * (float) $this->settings->get('loyalty_redeem_rate', 0.5), 2);
    }

    /** How many points a given rupee amount is worth to redeem (rounded down — never round in the customer's favor). */
    public function rupeesToPoints(float $rupees): int
    {
        $rate = (float) $this->settings->get('loyalty_redeem_rate', 0.5);
        return $rate > 0 ? (int) floor($rupees / $rate) : 0;
    }

    public function minRedeem(): int
    {
        return (int) $this->settings->get('loyalty_min_redeem', 100);
    }

    /**
     * Awards points for a delivered order. Idempotent via the
     * orders.loyalty_credited flag — safe even if delivery status
     * gets set more than once.
     */
    public function awardForOrder(int $orderId, int $userId, float $orderTotal): void
    {
        if (!$this->isEnabled()) return;

        $rate   = (float) $this->settings->get('loyalty_earn_rate', 1);
        $points = (int) floor($orderTotal / 100 * $rate);
        if ($points <= 0) return;

        $this->loyalty->credit($userId, $points, $orderId, "Earned on order #{$orderId}");
        (new NotificationService())->notify(
            'customer', $userId, 'loyalty',
            'Points earned!',
            "You earned {$points} loyalty points on your recent order.",
            \APP_URL . '/account/loyalty'
        );
    }

    /**
     * Validates a redemption request against balance and the minimum
     * threshold, returning the rupee discount it's worth (capped at
     * the order's remaining payable amount by the caller).
     */
    public function validateRedemption(int $userId, int $pointsRequested): array
    {
        if (!$this->isEnabled()) {
            return ['valid' => false, 'message' => 'Loyalty points are not currently enabled.'];
        }
        if ($pointsRequested < $this->minRedeem()) {
            return ['valid' => false, 'message' => 'Minimum ' . $this->minRedeem() . ' points required to redeem.'];
        }
        $balance = $this->balance($userId);
        if ($pointsRequested > $balance) {
            return ['valid' => false, 'message' => 'You only have ' . $balance . ' points available.'];
        }
        return ['valid' => true, 'discount' => $this->pointsToRupees($pointsRequested)];
    }

    public function redeem(int $userId, int $points, int $orderId): int
    {
        return $this->loyalty->redeem($userId, $points, $orderId, "Redeemed on order #{$orderId}");
    }
}
