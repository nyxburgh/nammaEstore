<?php
namespace App\Core\Interfaces;

/**
 * Every shipping/courier provider (manual self-ship, Shiprocket,
 * Delhivery, ...) implements this.
 */
interface ShippingInterface
{
    /**
     * Register a shipment with the provider. Returns
     * ['tracking_number' => ..., 'tracking_url' => ..., 'courier_name' => ...].
     */
    public function createShipment(array $details): array;

    /**
     * Fetch the current status of a shipment from the provider.
     * Returns one of: pending|picked_up|in_transit|out_for_delivery|delivered|failed|rto
     */
    public function getStatus(string $trackingNumber): string;
}
