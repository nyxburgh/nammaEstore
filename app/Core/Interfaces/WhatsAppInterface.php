<?php
namespace App\Core\Interfaces;

/**
 * Every WhatsApp provider (Meta Cloud API, Gupshup, Interakt...) implements this.
 */
interface WhatsAppInterface
{
    /**
     * Send a free-form text message (only valid inside a 24h session window).
     */
    public function sendText(string $phone, string $message): bool;

    /**
     * Send a pre-approved template message (required to initiate outside
     * the 24h session window, e.g. order confirmations, OTPs).
     */
    public function sendTemplate(string $phone, string $templateName, array $params = []): bool;
}
