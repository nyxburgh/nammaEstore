<?php
namespace App\VendorPanel\Services;

use App\Repositories\{VendorWalletRepository, VendorSettlementRepository, VendorWithdrawalRepository, SettingsRepository, UserRepository};

class WalletService
{
    private VendorWalletRepository $wallets;
    private VendorSettlementRepository $settlements;
    private VendorWithdrawalRepository $withdrawals;
    private SettingsRepository $settings;
    private UserRepository $users;

    public function __construct()
    {
        $this->wallets      = new VendorWalletRepository();
        $this->settlements  = new VendorSettlementRepository();
        $this->withdrawals  = new VendorWithdrawalRepository();
        $this->settings     = new SettingsRepository();
        $this->users        = new UserRepository();
    }

    public function overview(int $vendorId): array
    {
        return [
            'wallet'   => $this->wallets->getOrCreate($vendorId),
            'pipeline' => $this->settlements->getPendingSummary($vendorId),
            'min_withdrawal' => (float) $this->settings->get('settlement_min_withdrawal', 1000),
        ];
    }

    public function transactions(int $vendorId, int $page): array
    {
        return $this->wallets->getTransactions($vendorId, $page);
    }

    public function settlementHistory(int $vendorId, int $page, array $filters): array
    {
        return $this->settlements->getForVendor($vendorId, $page, $filters);
    }

    public function withdrawalHistory(int $vendorId, int $page): array
    {
        return $this->withdrawals->getForVendor($vendorId, $page);
    }

    /**
     * Vendor requests a withdrawal. Validates: minimum amount, and
     * that the requested amount doesn't exceed (wallet balance minus
     * anything already tied up in a pending request).
     */
    public function requestWithdrawal(int $vendorId, float $amount, string $method, string $methodDetails): array
    {
        $min = (float) $this->settings->get('settlement_min_withdrawal', 1000);
        if ($amount < $min) {
            return ['success' => false, 'message' => 'Minimum withdrawal amount is ₹' . number_format($min, 2) . '.'];
        }

        $wallet = $this->wallets->getOrCreate($vendorId);
        $alreadyPending = $this->withdrawals->pendingTotalForVendor($vendorId);
        $available = (float) $wallet['balance'] - $alreadyPending;

        if ($amount > $available) {
            return ['success' => false, 'message' => 'Amount exceeds your available balance (₹' . number_format($available, 2) . ').'];
        }

        if (!in_array($method, ['upi', 'bank_transfer', 'wallet'], true)) {
            return ['success' => false, 'message' => 'Invalid payout method.'];
        }

        $this->withdrawals->insert([
            'vendor_id'      => $vendorId,
            'amount'         => $amount,
            'method'         => $method,
            'method_details' => $methodDetails,
            'status'         => 'pending',
        ]);

        return ['success' => true, 'message' => 'Withdrawal request submitted. It will be reviewed by the admin.'];
    }
}
