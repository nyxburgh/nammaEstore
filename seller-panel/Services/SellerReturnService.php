<?php
namespace App\SellerPanel\Services;

use App\Repositories\ReturnRepository;
use App\Core\Services\NotificationService;

/**
 * Seller-side review step for return/replacement requests. A return
 * starts 'requested'; the seller here approves or rejects it before
 * an admin can process the actual refund (see admin's ReturnService::
 * markRefunded(), which now requires status='approved' first).
 */
class SellerReturnService
{
    private ReturnRepository $returns;

    public function __construct()
    {
        $this->returns = new ReturnRepository();
    }

    public function list(int $sellerId, int $page, array $filters): array
    {
        return $this->returns->getForSeller($sellerId, $page, $filters);
    }

    public function approve(int $id, int $sellerId): array
    {
        $r = $this->ownedPendingRequest($id, $sellerId);
        if (!$r) return ['success' => false, 'message' => 'Request not found or already resolved.'];

        $this->returns->updateSellerStatus($id, 'approved', $sellerId);
        // A cancellation restores stock immediately — it was never
        // shipped, unlike a physical return that comes back later.
        if ($r['type'] === 'cancel') {
            $this->returns->restockItem((int) $r['order_item_id']);
        }
        $this->notify($id, 'Return approved', 'The seller approved your return/replacement request. Your refund is now being processed.');
        return ['success' => true];
    }

    public function reject(int $id, int $sellerId): array
    {
        $r = $this->ownedPendingRequest($id, $sellerId);
        if (!$r) return ['success' => false, 'message' => 'Request not found or already resolved.'];

        $this->returns->updateSellerStatus($id, 'rejected', $sellerId);
        $this->notify($id, 'Return rejected', 'The seller was unable to approve your return/replacement request. Contact support for details.');
        return ['success' => true];
    }

    /** A seller may only act on their own 'requested' returns — not ones already resolved or belonging to another seller. */
    private function ownedPendingRequest(int $id, int $sellerId): ?array
    {
        $r = $this->returns->findById($id);
        if (!$r || (int) $r['seller_id'] !== $sellerId || $r['status'] !== 'requested') return null;
        return $r;
    }

    private function notify(int $returnId, string $title, string $message): void
    {
        $r = $this->returns->findById($returnId);
        if (!$r) return;
        (new NotificationService())->notify('customer', (int) $r['user_id'], 'return', $title, $message, APP_URL . '/account/returns');
    }
}
