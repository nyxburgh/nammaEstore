<?php
namespace App\Core\Services;

use App\Repositories\{InvoiceRepository, SettingsRepository};

/**
 * Renders a GST invoice to a downloadable file.
 *
 * Uses Dompdf (via `composer require dompdf/dompdf`) when available
 * for a real PDF. Without it, degrades gracefully to a styled HTML
 * file — still fully downloadable and printable, just not a .pdf.
 * This mirrors the same graceful-degradation pattern used by
 * SmtpEmailProvider for PHPMailer.
 */
class InvoicePdfService
{
    private InvoiceRepository $invoices;
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->invoices = new InvoiceRepository();
        $this->settings = new SettingsRepository();
    }

    /**
     * Renders (or returns the already-rendered) file for an invoice.
     * Returns the path relative to UPLOAD_PATH, or null on failure.
     */
    public function render(int $invoiceId): ?string
    {
        $invoice = $this->invoices->findWithItems($invoiceId);
        if (!$invoice) return null;

        if (!empty($invoice['pdf_path']) && is_file(UPLOAD_PATH . '/' . $invoice['pdf_path'])) {
            return $invoice['pdf_path'];
        }

        $company = $this->settings->getAllGrouped()['gst'] ?? [];
        // getAllGrouped returns row arrays; flatten to key => value for the template.
        $companyFlat = [];
        foreach ($company as $row) {
            $companyFlat[$row['key']] = $row['value'];
        }

        $html = $this->renderHtml($invoice, $companyFlat);

        $dir = UPLOAD_PATH . '/invoices';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $relative = 'invoices/' . $invoice['invoice_number'] . '.pdf';
            file_put_contents($dir . '/' . $invoice['invoice_number'] . '.pdf', $dompdf->output());
        } else {
            $relative = 'invoices/' . $invoice['invoice_number'] . '.html';
            file_put_contents($dir . '/' . $invoice['invoice_number'] . '.html', $html);
        }

        $this->invoices->setPdfPath($invoiceId, $relative);
        return $relative;
    }

    private function renderHtml(array $invoice, array $company): string
    {
        ob_start();
        include BASE_PATH . '/app/Core/invoice-templates/invoice.php';
        return ob_get_clean();
    }

    /** Emails the invoice PDF/HTML as an attachment to the customer. */
    public function emailInvoice(int $invoiceId): bool
    {
        $invoice = $this->invoices->findWithItems($invoiceId);
        if (!$invoice) return false;

        $path = $this->render($invoiceId);
        if (!$path) return false;

        try {
            $sent = \App\Core\ProviderFactory::email()->send(
                $invoice['customer_email'],
                'Tax Invoice ' . $invoice['invoice_number'] . ' — Order #' . $invoice['order_number'],
                '<p>Hi ' . e($invoice['customer_name']) . ',</p><p>Please find attached the tax invoice for your recent order.</p>',
                [['path' => UPLOAD_PATH . '/' . $path, 'name' => basename($path)]]
            );
        } catch (\Exception $e) {
            error_log('Invoice email failed for invoice ' . $invoiceId . ': ' . $e->getMessage());
            return false;
        }

        if ($sent) $this->invoices->markEmailed($invoiceId);
        return $sent;
    }
}
