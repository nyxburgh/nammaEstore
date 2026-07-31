<?php
namespace App\Core\Interfaces;

/**
 * Every email provider (SMTP, Amazon SES, Mailgun, Brevo...) implements this.
 */
interface EmailInterface
{
    /**
     * Send an HTML email. $attachments is an array of ['path' => ..., 'name' => ...].
     */
    public function send(string $to, string $subject, string $htmlBody, array $attachments = []): bool;

    /**
     * Send using a provider-side template (some providers, e.g. Brevo/SES,
     * support server-side templates with variable substitution).
     */
    public function sendTemplate(string $to, string $templateId, array $vars = []): bool;
}
