<?php
namespace App\Admin\Services;

use App\Repositories\{SellerWithdrawalRepository, SellerWalletRepository, SettingsRepository};

class WithdrawalService
{
    private SellerWithdrawalRepository $withdrawals;
    private SellerWalletRepository $wallets;
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->withdrawals = new SellerWithdrawalRepository();
        $this->wallets      = new SellerWalletRepository();
        $this->settings     = new SettingsRepository();
    }

    public function queue(int $page, array $filters): array
    {
        return $this->withdrawals->getQueue($page, $filters);
    }

    public function approve(int $id, int $adminId): array
    {
        $w = $this->withdrawals->findById($id);
        if (!$w || $w['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Request is not pending.'];
        }
        // Approving debits the wallet immediately — the seller's
        // balance reflects money that's committed to payout, even
        // before the bank transfer/UPI settlement is manually confirmed.
        try {
            $this->wallets->debit(
                (int) $w['seller_id'], (float) $w['amount'], 'withdrawal', $id,
                'Withdrawal approved via ' . $w['method']
            );
        } catch (\RuntimeException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
        $this->withdrawals->markProcessed($id, 'approved', $adminId);
        $this->notifySeller((int) $w['seller_id'], 'Withdrawal approved', 'Your withdrawal of ' . currency((float) $w['amount']) . ' has been approved.');
        return ['success' => true];
    }

    public function reject(int $id, int $adminId, string $note): array
    {
        $w = $this->withdrawals->findById($id);
        if (!$w || $w['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Request is not pending.'];
        }
        $this->withdrawals->markProcessed($id, 'rejected', $adminId, $note);
        $this->notifySeller((int) $w['seller_id'], 'Withdrawal rejected', 'Your withdrawal of ' . currency((float) $w['amount']) . ' was rejected.' . ($note ? " Reason: {$note}" : ''));
        return ['success' => true];
    }

    /** Marks an already-approved withdrawal as actually paid out (bank/UPI transfer confirmed). */
    public function markPaid(int $id, int $adminId): array
    {
        $w = $this->withdrawals->findById($id);
        if (!$w || $w['status'] !== 'approved') {
            return ['success' => false, 'message' => 'Request must be approved first.'];
        }
        $this->withdrawals->markProcessed($id, 'paid', $adminId);
        $this->notifySeller((int) $w['seller_id'], 'Withdrawal paid', currency((float) $w['amount']) . ' has been transferred to you.');
        return ['success' => true];
    }

    private function notifySeller(int $sellerId, string $title, string $message): void
    {
        (new \App\Core\Services\NotificationService())->notify('seller', $sellerId, 'withdrawal', $title, $message, SELLER_URL . '/wallet');
    }
}
