<?php
namespace App\Core\Services;

use App\Core\{Database, ProviderFactory};
use App\Repositories\ShipmentRepository;

class ShipmentService
{
    private Database $db;
    private ShipmentRepository $shipments;

    public function __construct()
    {
        $this->db        = Database::getInstance();
        $this->shipments = new ShipmentRepository();
    }

    /**
     * Create a shipment for a vendor's portion of an order. Goes
     * through ShippingInterface (currently the manual provider — the
     * vendor/admin types in courier + tracking number) so a real
     * courier aggregator can be swapped in later without touching
     * this method.
     */
    public function create(int $orderId, int $vendorId, int $splitId, array $details): array
    {
        if ($this->shipments->findBySplitId($splitId)) {
            return ['success' => false, 'message' => 'A shipment already exists for this order.'];
        }

        $result = ProviderFactory::shipping()->createShipment($details);

        $shipmentId = $this->shipments->createWithTracking([
            'order_id'              => $orderId,
            'order_vendor_split_id' => $splitId,
            'vendor_id'             => $vendorId,
            'courier_name'          => $result['courier_name'],
            'tracking_number'       => $result['tracking_number'],
            'tracking_url'          => $result['tracking_url'],
            'status'                => 'pending',
            'weight_kg'             => $details['weight_kg'] ?? null,
            'dimensions'            => $details['dimensions'] ?? null,
            'shipping_charge'       => $details['shipping_charge'] ?? 0,
        ]);

        // Mark the order shipped and notify the customer with tracking info.
        $order = $this->db->fetchOne("SELECT order_number, user_id FROM `" . DB_PREFIX . "orders` WHERE id=?", [$orderId]);
        if ($order) {
            $notif = new NotificationService();
            $notif->notify(
                'customer', (int) $order['user_id'], 'shipment',
                'Your order has shipped! #' . $order['order_number'],
                'Tracking: ' . $result['courier_name'] . ' — ' . $result['tracking_number'],
                APP_URL . '/track/' . $order['order_number']
            );
        }

        return ['success' => true, 'shipment_id' => $shipmentId];
    }

    public function addTrackingUpdate(int $shipmentId, string $status, ?string $location, ?string $note): array
    {
        $shipment = $this->shipments->findById($shipmentId);
        if (!$shipment) {
            return ['success' => false, 'message' => 'Shipment not found.'];
        }

        $this->shipments->addTrackingUpdate($shipmentId, $status, $location, $note);

        $order = $this->db->fetchOne("SELECT order_number, user_id FROM `" . DB_PREFIX . "orders` WHERE id=?", [$shipment['order_id']]);
        if ($order && in_array($status, ['out_for_delivery', 'delivered', 'failed', 'rto'], true)) {
            $labels = [
                'out_for_delivery' => 'is out for delivery',
                'delivered'        => 'has been delivered',
                'failed'           => 'delivery attempt failed',
                'rto'              => 'is being returned to the vendor',
            ];
            (new NotificationService())->notify(
                'customer', (int) $order['user_id'], 'shipment',
                'Shipment update: #' . $order['order_number'],
                'Your shipment ' . $labels[$status] . '.',
                APP_URL . '/track/' . $order['order_number']
            );
        }

        return ['success' => true];
    }

    public function findForVendor(int $id, int $vendorId): ?array
    {
        $s = $this->shipments->findWithTracking($id);
        return ($s && (int) $s['vendor_id'] === $vendorId) ? $s : null;
    }
}
