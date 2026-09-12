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

    /**
     * Admin can still reject a request outright (e.g. obviously
     * fraudulent) whether or not the seller has weighed in yet —
     * but approval (see SellerReturnService::approve()) is the
     * seller's call first; admin's role is processing the refund
     * once approved, not approving it themselves.
     */
    public function reject(int $id, int $adminId): void
    {
        $this->returns->updateStatus($id, 'rejected', $adminId);
        $this->notify($id, 'Return rejected', 'Your return/replacement request was not approved. Contact support for details.');
    }

    /**
     * Marks the return refunded AND credits the customer's wallet —
     * without this, "Refund" would just be a status label with no
     * actual money movement. Only allowed once the seller has
     * approved the request first (status='approved') — enforces the
     * "seller approves, then admin refunds" order server-side, not
     * just by hiding the button in the UI.
     */
    public function markRefunded(int $id, int $adminId, float $refundAmount): array
    {
        $r = $this->returns->findById($id);
        if (!$r) return ['success' => false, 'message' => 'Return request not found.'];
        if ($r['status'] !== 'approved') {
            return ['success' => false, 'message' => 'This request must be approved by the seller before it can be refunded.'];
        }

        $this->returns->updateStatus($id, 'refunded', $adminId, $refundAmount);

        if ($refundAmount > 0) {
            $this->wallets->credit(
                (int) $r['user_id'], $refundAmount, 'refund', $id,
                'Refund for return #' . $id
            );
        }
        // Physical return means the item comes back to the seller —
        // restock it. (Replacements aren't restocked here: a new unit
        // already went out to replace the defective one, netting to
        // no stock change worth automating.)
        if ($r['type'] === 'return') {
            $this->returns->restockItem((int) $r['order_item_id']);
        }
        $this->notify($id, 'Refund processed', currency($refundAmount) . ' has been credited to your wallet.');
        return ['success' => true];
    }

    private function notify(int $returnId, string $title, string $message): void
    {
        $r = $this->returns->findById($returnId);
        if (!$r) return;
        (new NotificationService())->notify('customer', (int) $r['user_id'], 'return', $title, $message, APP_URL . '/account/returns');
    }
}
