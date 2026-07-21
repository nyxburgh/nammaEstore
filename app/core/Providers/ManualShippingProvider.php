<?php
namespace App\Core\Providers;

use App\Core\Interfaces\ShippingInterface;

/**
 * "Manual" shipping — the seller/admin types in the courier name and
 * tracking number themselves (e.g. India Post, a local courier, or
 * self-delivery), rather than calling an aggregator API. This is the
 * default provider since most small/medium sellers on a marketplace
 * don't have their own Shiprocket/Delhivery API credentials.
 *
 * A real aggregator integration (ShiprocketProvider, DelhiveryProvider)
 * can be added later as another class implementing ShippingInterface —
 * nothing in the Services/Controllers layer needs to change, only the
 * "active" key in config/providers.php.
 */
class ManualShippingProvider implements ShippingInterface
{
    public function createShipment(array $details): array
    {
        return [
            'tracking_number' => $details['tracking_number'] ?? '',
            'tracking_url'    => $details['tracking_url'] ?? null,
            'courier_name'    => $details['courier_name'] ?? 'Manual / Self-Ship',
        ];
    }

    public function getStatus(string $trackingNumber): string
    {
        // Manual shipments have no external API to poll — status is
        // updated by whoever is shipping it, via the admin/seller panel.
        return 'pending';
    }
}
