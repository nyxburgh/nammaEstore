<?php
namespace App\Core\Providers;

use App\Core\Interfaces\WhatsAppInterface;

class MetaWhatsAppProvider implements WhatsAppInterface
{
    private string $phoneNumberId;
    private string $accessToken;
    private string $baseUrl = 'https://graph.facebook.com/v19.0';

    public function __construct(string $phoneNumberId, string $accessToken)
    {
        $this->phoneNumberId = $phoneNumberId;
        $this->accessToken   = $accessToken;
    }

    public function sendText(string $phone, string $message): bool
    {
        $res = $this->call([
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalize($phone),
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);
        return isset($res['messages'][0]['id']);
    }

    public function sendTemplate(string $phone, string $templateName, array $params = []): bool
    {
        $components = [];
        if (!empty($params)) {
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(fn($v) => ['type' => 'text', 'text' => (string) $v], $params),
            ];
        }
        $res = $this->call([
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalize($phone),
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => 'en'],
                'components' => $components,
            ],
        ]);
        return isset($res['messages'][0]['id']);
    }

    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return str_starts_with($digits, '91') ? $digits : '91' . $digits;
    }

    private function call(array $body): array
    {
        $ch = curl_init("{$this->baseUrl}/{$this->phoneNumberId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->accessToken,
            ],
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_TIMEOUT    => 15,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        return json_decode($raw ?: '{}', true) ?? [];
    }
}
