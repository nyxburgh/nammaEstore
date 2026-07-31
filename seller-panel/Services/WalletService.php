<?php
namespace App\SellerPanel\Services;

use App\Repositories\{SellerWalletRepository, SellerSettlementRepository, SellerWithdrawalRepository, SettingsRepository, UserRepository};

class WalletService
{
    private SellerWalletRepository $wallets;
    private SellerSettlementRepository $settlements;
    private SellerWithdrawalRepository $withdrawals;
    private SettingsRepository $settings;
    private UserRepository $users;

    public function __construct()
    {
        $this->wallets      = new SellerWalletRepository();
        $this->settlements  = new SellerSettlementRepository();
        $this->withdrawals  = new SellerWithdrawalRepository();
        $this->settings     = new SettingsRepository();
        $this->users        = new UserRepository();
    }

    public function overview(int $sellerId): array
    {
        return [
            'wallet'   => $this->wallets->getOrCreate($sellerId),
            'pipeline' => $this->settlements->getPendingSummary($sellerId),
            'min_withdrawal' => (float) $this->settings->get('settlement_min_withdrawal', 1000),
        ];
    }

    public function transactions(int $sellerId, int $page): array
    {
        return $this->wallets->getTransactions($sellerId, $page);
    }

    public function settlementHistory(int $sellerId, int $page, array $filters): array
    {
        return $this->settlements->getForSeller($sellerId, $page, $filters);
    }

    public function withdrawalHistory(int $sellerId, int $page): array
    {
        return $this->withdrawals->getForSeller($sellerId, $page);
    }

    /**
     * Seller requests a withdrawal. Validates: minimum amount, and
     * that the requested amount doesn't exceed (wallet balance minus
     * anything already tied up in a pending request).
     */
    public function requestWithdrawal(int $sellerId, float $amount, string $method, string $methodDetails): array
    {
        $min = (float) $this->settings->get('settlement_min_withdrawal', 1000);
        if ($amount < $min) {
            return ['success' => false, 'message' => 'Minimum withdrawal amount is ₹' . number_format($min, 2) . '.'];
        }

        $wallet = $this->wallets->getOrCreate($sellerId);
        $alreadyPending = $this->withdrawals->pendingTotalForSeller($sellerId);
        $available = (float) $wallet['balance'] - $alreadyPending;

        if ($amount > $available) {
            return ['success' => false, 'message' => 'Amount exceeds your available balance (₹' . number_format($available, 2) . ').'];
        }

        if (!in_array($method, ['upi', 'bank_transfer', 'wallet'], true)) {
            return ['success' => false, 'message' => 'Invalid payout method.'];
        }

        $this->withdrawals->insert([
            'seller_id'      => $sellerId,
            'amount'         => $amount,
            'method'         => $method,
            'method_details' => $methodDetails,
            'status'         => 'pending',
        ]);

        return ['success' => true, 'message' => 'Withdrawal request submitted. It will be reviewed by the admin.'];
    }
}
