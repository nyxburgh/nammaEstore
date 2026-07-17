<?php
namespace App\Admin\Services;

use App\Repositories\{
    OrderVendorSplitRepository,
    VendorSettlementRepository,
    VendorWalletRepository,
    ReturnRepository,
    DisputeRepository,
    SettingsRepository,
    UserRepository
};

/**
 * The vendor settlement engine.
 *
 * Implements the spec's waterfall exactly:
 *   Order Amount
 *   (-) Tax            [not itemized separately in current schema —
 *                        product prices are tax-inclusive; kept as a
 *                        line for when GST module (Phase 4) splits it out]
 *   (-) Shipping
 *   (-) Platform Charges
 *   (-) Commission
 *   (-) Penalty
 *   (-) Refund
 *   (-) Subscription Due
 *   = Vendor Payable
 *
 * And the eligibility gate:
 *   - Order must be delivered AND paid
 *   - Return window (days since delivery) must be closed
 *   - No open return/replacement/cancel request on any item in the split
 *   - No open dispute on the order for this vendor
 *   - If vendor_type = 'subscription': the vendor's subscription must
 *     currently be ACTIVE, or the entire settlement is blocked
 *     (not just charged a fallback commission — spec is explicit that
 *     a lapsed subscription blocks settlement outright).
 *
 * Run this periodically (cron) via runEligibilityPass(), and run
 * creditEligibleToWallets() to move anything that became eligible
 * into vendor wallets. Both are also safe to trigger manually from
 * the admin settlement dashboard.
 */
class SettlementService
{
    private OrderVendorSplitRepository $splits;
    private VendorSettlementRepository $settlements;
    private VendorWalletRepository $wallets;
    private ReturnRepository $returns;
    private DisputeRepository $disputes;
    private SettingsRepository $settings;
    private UserRepository $users;

    public function __construct()
    {
        $this->splits      = new OrderVendorSplitRepository();
        $this->settlements = new VendorSettlementRepository();
        $this->wallets      = new VendorWalletRepository();
        $this->returns      = new ReturnRepository();
        $this->disputes     = new DisputeRepository();
        $this->settings     = new SettingsRepository();
        $this->users        = new UserRepository();
    }

    /**
     * Evaluate every delivered-and-paid split that hasn't been paid
     * out yet. For each: compute the waterfall, decide eligibility,
     * and upsert a vendor_settlements row reflecting current status.
     * Does NOT move money — see creditEligibleToWallets() for that.
     */
    public function runEligibilityPass(): array
    {
        $candidates = $this->splits->getCandidatesForSettlement();
        $result = ['evaluated' => 0, 'eligible' => 0, 'held' => 0, 'not_yet' => 0];

        foreach ($candidates as $split) {
            $result['evaluated']++;
            $eval = $this->evaluateSplit($split);

            $this->settlements->upsert([
                'vendor_id'             => $split['vendor_id'],
                'order_id'              => $split['order_id'],
                'order_vendor_split_id' => $split['id'],
                'gross_amount'          => $split['gross_amount'],
                'tax_amount'            => 0.00,
                'shipping_amount'       => $eval['shipping_amount'],
                'platform_charge'       => $eval['platform_charge'],
                'commission_amount'     => $split['commission_amount'],
                'penalty_amount'        => $eval['penalty_amount'],
                'refund_amount'         => $eval['refund_amount'],
                'subscription_due'      => $eval['subscription_due'],
                'net_payable'           => $eval['net_payable'],
                'status'                => $eval['status'],
                'hold_reason'           => $eval['hold_reason'],
                'eligible_at'           => $eval['status'] === 'eligible' ? date('Y-m-d H:i:s') : null,
            ]);

            match ($eval['status']) {
                'eligible' => $result['eligible']++,
                'on_hold'  => $result['held']++,
                default    => $result['not_yet']++,
            };
        }

        return $result;
    }

    /**
     * Move every 'eligible' settlement into the vendor's wallet and
     * mark the underlying split as paid out of the pending queue.
     * This is the actual money-movement step (wallet balance, not a
     * bank transfer — the vendor withdraws from the wallet separately).
     */
    public function creditEligibleToWallets(): array
    {
        $eligible = $this->settlements->getUncreditedEligible();
        $credited = 0;
        $totalAmount = 0.0;

        foreach ($eligible as $s) {
            if ((float) $s['net_payable'] <= 0) {
                // Nothing payable (e.g. fully refunded) — mark credited with 0 effect.
                $this->settlements->markCredited($s['id']);
                continue;
            }
            $this->wallets->credit(
                (int) $s['vendor_id'],
                (float) $s['net_payable'],
                'settlement',
                (int) $s['id'],
                'Settlement for order #' . $s['order_id']
            );
            $this->splits->markPaid((int) $s['order_vendor_split_id']);
            $this->settlements->markCredited((int) $s['id']);
            $credited++;
            $totalAmount += (float) $s['net_payable'];
        }

        return ['credited' => $credited, 'total_amount' => $totalAmount];
    }

    /**
     * The eligibility + waterfall calculation for a single split.
     * Pulled out as its own method so it's independently testable.
     */
    private function evaluateSplit(array $split): array
    {
        $vendorId = (int) $split['vendor_id'];
        $orderId  = (int) $split['order_id'];

        // ── Gate 1: delivered + paid already guaranteed by the
        // candidate query, but re-check defensively.
        if ($split['order_status'] !== 'delivered' || $split['payment_status'] !== 'paid') {
            return $this->notYet('Order not yet delivered/paid.', $split);
        }

        // ── Gate 2: return window must be closed ──────────────
        $windowDays = (int) $this->settings->get('settlement_return_window_days', 7);
        $deliveredAt = $split['delivered_at'] ?? null;
        if (!$deliveredAt) {
            return $this->notYet('Delivery date not recorded yet.', $split);
        }
        $windowClosesAt = strtotime($deliveredAt) + ($windowDays * 86400);
        if (time() < $windowClosesAt) {
            return $this->notYet('Return window open until ' . date('Y-m-d', $windowClosesAt) . '.', $split);
        }

        // ── Gate 3: no open return/replacement/cancel on any item ──
        $itemIds = $this->splits->getItemIds($orderId, $vendorId);
        $refundAmount = 0.0;
        foreach ($itemIds as $itemId) {
            if ($this->returns->hasOpenRequest($itemId)) {
                return $this->hold('Open return/replacement request on order item #' . $itemId . '.', $split);
            }
            $refundAmount += $this->returns->getRefundAmount($itemId);
        }

        // ── Gate 4: no open dispute ─────────────────────────────
        if ($this->disputes->hasOpenDispute($orderId, $vendorId)) {
            return $this->hold('Open dispute on this order.', $split);
        }

        // ── Gate 5: subscription vendors must have an active plan ──
        $subscriptionDue = 0.00;
        if (($split['vendor_type'] ?? 'commission') === 'subscription') {
            $profile = $this->users->getVendorProfile($vendorId);
            $isActive = !empty($profile['expires_at']) && strtotime($profile['expires_at']) > time();
            if (!$isActive) {
                return $this->hold('Subscription is not active — settlement blocked until renewed.', $split);
            }
            // Subscription vendors pay 0% commission while active; any
            // commission_amount already stored on the split (e.g. from
            // a stale calculation) is instead treated as the
            // subscription due line so the waterfall stays honest.
            $subscriptionDue = (float) $split['commission_amount'];
        }

        // ── All gates passed — compute the waterfall ────────────
        $gross          = (float) $split['gross_amount'];
        $shippingAmount = 0.00; // shipping is charged at order level, not per-vendor-split, in the current schema
        $platformPct    = (float) $this->settings->get('settlement_platform_charge_pct', 0);
        $platformCharge = round($gross * $platformPct / 100, 2);
        $commission     = ($split['vendor_type'] ?? 'commission') === 'subscription' ? 0.00 : (float) $split['commission_amount'];
        $penalty        = 0.00; // penalty engine is a future phase hook; kept as an explicit waterfall line now

        $netPayable = round(
            $gross - $shippingAmount - $platformCharge - $commission - $penalty - $refundAmount - $subscriptionDue,
            2
        );

        return [
            'status'            => 'eligible',
            'hold_reason'       => null,
            'shipping_amount'   => $shippingAmount,
            'platform_charge'   => $platformCharge,
            'penalty_amount'    => $penalty,
            'refund_amount'     => $refundAmount,
            'subscription_due'  => $subscriptionDue,
            'net_payable'       => max(0, $netPayable),
        ];
    }

    private function notYet(string $reason, array $split): array
    {
        return [
            'status' => 'not_eligible', 'hold_reason' => $reason,
            'shipping_amount' => 0, 'platform_charge' => 0, 'penalty_amount' => 0,
            'refund_amount' => 0, 'subscription_due' => 0, 'net_payable' => 0,
        ];
    }

    private function hold(string $reason, array $split): array
    {
        return [
            'status' => 'on_hold', 'hold_reason' => $reason,
            'shipping_amount' => 0, 'platform_charge' => 0, 'penalty_amount' => 0,
            'refund_amount' => 0, 'subscription_due' => 0, 'net_payable' => 0,
        ];
    }
}
