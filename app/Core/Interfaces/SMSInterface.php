<?php
namespace App\Core\Interfaces;

/**
 * Every SMS provider (MSG91, Fast2SMS, TextLocal...) implements this.
 */
interface SMSInterface
{
    /**
     * Send a plain-text SMS. Returns true on accepted-for-delivery.
     */
    public function send(string $phone, string $message): bool;

    /**
     * Send using a pre-approved DLT template (required by Indian carriers).
     * $params fills the template placeholders in order.
     */
    public function sendTemplate(string $phone, string $templateId, array $params = []): bool;

    /**
     * Convenience wrapper most callers actually want.
     */
    public function sendOtp(string $phone, string $otp): bool;
}
