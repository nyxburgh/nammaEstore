<?php
namespace App\Admin\Services;

use App\Repositories\{VendorWithdrawalRepository, VendorWalletRepository, SettingsRepository};

class WithdrawalService
{
    private VendorWithdrawalRepository $withdrawals;
    private VendorWalletRepository $wallets;
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->withdrawals = new VendorWithdrawalRepository();
        $this->wallets      = new VendorWalletRepository();
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
        // Approving debits the wallet immediately — the vendor's
        // balance reflects money that's committed to payout, even
        // before the bank transfer/UPI settlement is manually confirmed.
        try {
            $this->wallets->debit(
                (int) $w['vendor_id'], (float) $w['amount'], 'withdrawal', $id,
                'Withdrawal approved via ' . $w['method']
            );
        } catch (\RuntimeException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
        $this->withdrawals->markProcessed($id, 'approved', $adminId);
        $this->notifyVendor((int) $w['vendor_id'], 'Withdrawal approved', 'Your withdrawal of ' . currency((float) $w['amount']) . ' has been approved.');
        return ['success' => true];
    }

    public function reject(int $id, int $adminId, string $note): array
    {
        $w = $this->withdrawals->findById($id);
        if (!$w || $w['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Request is not pending.'];
        }
        $this->withdrawals->markProcessed($id, 'rejected', $adminId, $note);
        $this->notifyVendor((int) $w['vendor_id'], 'Withdrawal rejected', 'Your withdrawal of ' . currency((float) $w['amount']) . ' was rejected.' . ($note ? " Reason: {$note}" : ''));
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
        $this->notifyVendor((int) $w['vendor_id'], 'Withdrawal paid', currency((float) $w['amount']) . ' has been transferred to you.');
        return ['success' => true];
    }

    private function notifyVendor(int $vendorId, string $title, string $message): void
    {
        (new \App\Core\Services\NotificationService())->notify('vendor', $vendorId, 'withdrawal', $title, $message, VENDOR_URL . '/wallet');
    }
}
