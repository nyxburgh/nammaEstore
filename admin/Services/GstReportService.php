<?php
namespace App\Admin\Services;

use App\Repositories\InvoiceRepository;

class GstReportService
{
    private InvoiceRepository $invoices;

    public function __construct()
    {
        $this->invoices = new InvoiceRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->invoices->getForAdmin($page, $filters);
    }

    public function summary(string $from, string $to): array
    {
        return $this->invoices->getGstSummary($from, $to);
    }

    /** Returns CSV content as a string, ready to stream for download. */
    public function exportCsv(string $from, string $to): string
    {
        $rows = $this->invoices->getForExport($from, $to);
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Invoice #', 'Date', 'Order #', 'Seller', 'Seller GSTIN', 'Place of Supply', 'Interstate', 'Taxable Amount', 'CGST', 'SGST', 'IGST', 'Grand Total']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['invoice_number'], $r['invoice_date'], $r['order_number'], $r['shop_name'] ?? '',
                $r['seller_gst'] ?? '', $r['place_of_supply'], $r['is_interstate'] ? 'Yes' : 'No',
                $r['taxable_amount'], $r['cgst_amount'], $r['sgst_amount'], $r['igst_amount'], $r['grand_total'],
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv;
    }
}
