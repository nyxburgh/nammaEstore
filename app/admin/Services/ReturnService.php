<?php
namespace App\Admin\Services;

use App\Repositories\{ReturnRepository, CustomerWalletRepository};
use App\Core\Services\NotificationService;

class ReturnService
{
    private ReturnRepository $returns;
    private CustomerWalletRepository $wallets;

    public function __construct()
    {
        $this->returns = new ReturnRepository();
        $this->wallets  = new CustomerWalletRepository();
    }

    public function queue(int $page, array $filters): array
    {
        return $this->returns->getQueue($page, $filters);
    }

    public function approve(int $id, int $adminId): void
    {
        $this->returns->updateStatus($id, 'approved', $adminId);

        // A cancellation restores the item to stock immediately —
        // it was never shipped, unlike a physical return.
        $r = $this->returns->findById($id);
        if ($r && $r['type'] === 'cancel') {
            $this->returns->restockItem((int) $r['order_item_id']);
        }

        $this->notify($id, 'Return approved', 'Your return/replacement request has been approved. We\'ll update you once it\'s picked up.');
    }

    public function reject(int $id, int $adminId): void
    {
        $this->returns->updateStatus($id, 'rejected', $adminId);
        $this->notify($id, 'Return rejected', 'Your return/replacement request was not approved. Contact support for details.');
    }

    /**
     * Marks the return refunded AND credits the customer's wallet —
     * without this, "Refund" would just be a status label with no
     * actual money movement.
     */
    public function markRefunded(int $id, int $adminId, float $refundAmount): void
    {
        $this->returns->updateStatus($id, 'refunded', $adminId, $refundAmount);

        $r = $this->returns->findById($id);
        if ($r && $refundAmount > 0) {
            $this->wallets->credit(
                (int) $r['user_id'], $refundAmount, 'refund', $id,
                'Refund for return #' . $id
            );
        }
        // Physical return means the item comes back to the seller —
        // restock it. (Replacements aren't restocked here: a new unit
        // already went out to replace the defective one, netting to
        // no stock change worth automating.)
        if ($r && $r['type'] === 'return') {
            $this->returns->restockItem((int) $r['order_item_id']);
        }
        $this->notify($id, 'Refund processed', currency($refundAmount) . ' has been credited to your wallet.');
    }

    private function notify(int $returnId, string $title, string $message): void
    {
        $r = $this->returns->findById($returnId);
        if (!$r) return;
        (new NotificationService())->notify('customer', (int) $r['user_id'], 'return', $title, $message, APP_URL . '/account/returns');
    }
}
