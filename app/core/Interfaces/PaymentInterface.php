<?php
namespace App\Core\Interfaces;

/**
 * Every payment gateway (Razorpay, PhonePe, Cashfree, PayU, Stripe...)
 * implements this. Services depend on this interface, never on a
 * concrete gateway class — swapping providers means adding a new
 * class here and changing one line in config, not touching Controllers
 * or Services.
 */
interface PaymentInterface
{
    /**
     * Create a payment order/intent with the gateway.
     * Returns an array the frontend uses to launch the gateway's checkout,
     * e.g. ['gateway_order_id' => '...', 'key' => '...', 'amount' => ...]
     */
    public function createOrder(float $amount, string $currency, array $meta = []): array;

    /**
     * Verify a callback/webhook signature from the gateway.
     */
    public function verifySignature(array $payload): bool;

    /**
     * Fetch the current status of a payment from the gateway.
     * Returns one of: pending|success|failed
     */
    public function getStatus(string $gatewayPaymentId): string;

    /**
     * Issue a full or partial refund.
     */
    public function refund(string $gatewayPaymentId, float $amount, string $reason = ''): array;
}
