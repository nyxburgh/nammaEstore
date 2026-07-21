<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\Core\Services\ShipmentService;

class ShipmentController extends SellerController
{
    public function create(): void
    {
        csrf_check();
        Middleware::sellerAuth();
        $r = (new ShipmentService())->create(
            (int) $this->input('order_id'),
            Auth::sellerId(),
            (int) $this->input('split_id'),
            [
                'courier_name'    => $this->input('courier_name'),
                'tracking_number' => $this->input('tracking_number'),
                'tracking_url'    => $this->input('tracking_url') ?: null,
                'weight_kg'       => $this->input('weight_kg') ?: null,
            ]
        );
        $this->json($r);
    }

    public function updateTracking(string $id): void
    {
        csrf_check();
        Middleware::sellerAuth();
        $svc = new ShipmentService();
        $shipment = $svc->findForSeller((int) $id, Auth::sellerId());
        if (!$shipment) { $this->json(['success' => false, 'message' => 'Shipment not found.']); return; }

        $r = $svc->addTrackingUpdate((int) $id, $this->input('status', ''), $this->input('location') ?: null, $this->input('note') ?: null);
        $this->json($r);
    }
}
