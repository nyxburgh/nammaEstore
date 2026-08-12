<?php
namespace App\Frontend\Services;

use App\Repositories\{ReturnRepository, OrderRepository};

class CustomerReturnService
{
    private ReturnRepository $returns;
    private OrderRepository $orders;

    public function __construct()
    {
        $this->returns = new ReturnRepository();
        $this->orders  = new OrderRepository();
    }

    public function myReturns(int $userId, int $page = 1): array
    {
        return $this->returns->getForCustomer($userId, $page);
    }

    /**
     * Customer submits a return/replacement/cancel request for a
     * specific item within one of their own orders. Ownership and
     * item membership are both verified server-side.
     */
    public function submit(int $userId, int $orderId, int $orderItemId, string $type, string $reason, string $note = ''): array
    {
        $order = $this->orders->getOrderDetail($orderId);
        if (!$order || (int) $order['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $item = null;
        foreach ($order['items'] as $i) {
            if ((int) $i['id'] === $orderItemId) { $item = $i; break; }
        }
        if (!$item) {
            return ['success' => false, 'message' => 'Order item not found.'];
        }

        if ($order['order_status'] !== 'delivered' && $type !== 'cancel') {
            return ['success' => false, 'message' => 'Only delivered orders can be returned or replaced.'];
        }
        if ($this->returns->hasOpenRequest($orderItemId)) {
            return ['success' => false, 'message' => 'A request for this item is already in progress.'];
        }

        $type = in_array($type, ['return', 'replacement', 'cancel'], true) ? $type : 'return';

        $this->returns->insert([
            'order_id'      => $orderId,
            'order_item_id' => $orderItemId,
            'user_id'       => $userId,
            'seller_id'     => $item['seller_id'],
            'type'          => $type,
            'reason'        => $reason,
            'note'          => $note,
            'status'        => 'requested',
        ]);

        (new \App\Core\Services\NotificationService())->notify(
            'seller', (int) $item['seller_id'], 'return_request',
            ucfirst($type) . ' request: #' . $order['order_number'],
            'A customer requested a ' . $type . ' for ' . ($item['product_name'] ?? 'a product') . '.',
            SELLER_URL . '/returns'
        );

        return ['success' => true, 'message' => 'Your request has been submitted and will be reviewed shortly.'];
    }
}
