<?php
namespace App\Core\Providers;

use App\Core\Interfaces\SMSInterface;

class Msg91SmsProvider implements SMSInterface
{
    private string $authKey;
    private string $senderId;
    private string $baseUrl = 'https://control.msg91.com/api/v5';

    public function __construct(string $authKey, string $senderId)
    {
        $this->authKey  = $authKey;
        $this->senderId = $senderId;
    }

    public function send(string $phone, string $message): bool
    {
        $res = $this->call('/sms/send', [
            'sender'   => $this->senderId,
            'route'    => '4',
            'country'  => '91',
            'sms'      => [['message' => $message, 'to' => [$this->normalize($phone)]]],
        ]);
        return ($res['type'] ?? '') === 'success';
    }

    public function sendTemplate(string $phone, string $templateId, array $params = []): bool
    {
        $recipient = ['mobiles' => $this->normalize($phone)];
        foreach ($params as $k => $v) {
            $recipient[$k] = $v;
        }
        $res = $this->call('/flow', [
            'template_id' => $templateId,
            'recipients'  => [$recipient],
        ]);
        return ($res['type'] ?? '') === 'success';
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        return $this->send($phone, "Your Namma E Store OTP is {$otp}. Do not share this with anyone.");
    }

    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return str_starts_with($digits, '91') ? $digits : '91' . $digits;
    }

    private function call(string $path, array $body): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'authkey: ' . $this->authKey],
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 15,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        return json_decode($raw ?: '{}', true) ?? [];
    }
}
