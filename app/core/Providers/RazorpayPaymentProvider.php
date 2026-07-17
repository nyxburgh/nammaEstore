<?php
namespace App\Core\Providers;

use App\Core\Interfaces\PaymentInterface;

class RazorpayPaymentProvider implements PaymentInterface
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct(string $keyId, string $keySecret)
    {
        $this->keyId     = $keyId;
        $this->keySecret = $keySecret;
    }

    public function createOrder(float $amount, string $currency, array $meta = []): array
    {
        $res = $this->call('POST', '/orders', [
            'amount'   => (int) round($amount * 100), // paise
            'currency' => $currency,
            'receipt'  => $meta['receipt'] ?? uniqid('ord_'),
            'notes'    => $meta,
        ]);

        return [
            'gateway_order_id' => $res['id'] ?? null,
            'key'              => $this->keyId,
            'amount'           => $res['amount'] ?? null,
            'currency'         => $res['currency'] ?? $currency,
            'raw'              => $res,
        ];
    }

    public function verifySignature(array $payload): bool
    {
        $expected = hash_hmac(
            'sha256',
            $payload['razorpay_order_id'] . '|' . $payload['razorpay_payment_id'],
            $this->keySecret
        );
        return hash_equals($expected, $payload['razorpay_signature'] ?? '');
    }

    public function getStatus(string $gatewayPaymentId): string
    {
        $res = $this->call('GET', "/payments/{$gatewayPaymentId}");
        return match ($res['status'] ?? '') {
            'captured' => 'success',
            'failed'   => 'failed',
            default    => 'pending',
        };
    }

    public function refund(string $gatewayPaymentId, float $amount, string $reason = ''): array
    {
        return $this->call('POST', "/payments/{$gatewayPaymentId}/refund", [
            'amount' => (int) round($amount * 100),
            'notes'  => ['reason' => $reason],
        ]);
    }

    private function call(string $method, string $path, array $body = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_USERPWD        => $this->keyId . ':' . $this->keySecret,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $method === 'GET' ? null : json_encode($body),
            CURLOPT_TIMEOUT        => 15,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        return json_decode($raw ?: '{}', true) ?? [];
    }
}
