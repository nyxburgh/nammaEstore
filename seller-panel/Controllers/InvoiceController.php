<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\Repositories\InvoiceRepository;
use App\Core\Services\InvoicePdfService;

class InvoiceController extends SellerController
{
    public function index(): void
    {
        Middleware::sellerAuth();
        $f = ['date_from' => $this->input('date_from', ''), 'date_to' => $this->input('date_to', '')];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'         => 'Invoices',
            'section'       => 'invoices',
            'invoicesData'  => (new InvoiceRepository())->getForSeller(Auth::sellerId(), (int) $this->input('page', 1), $f),
            'filters'       => $f,
        ]));
    }

    public function download(string $id): void
    {
        Middleware::sellerAuth();
        $invoice = (new InvoiceRepository())->findWithItems((int) $id);
        if (!$invoice || (int) $invoice['seller_id'] !== Auth::sellerId()) {
            http_response_code(404);
            echo 'Invoice not found.';
            return;
        }

        $path = (new InvoicePdfService())->render((int) $id);
        if (!$path) { http_response_code(500); echo 'Could not generate invoice.'; return; }

        $fullPath = UPLOAD_PATH . '/' . $path;
        header('Content-Type: ' . (str_ends_with($path, '.pdf') ? 'application/pdf' : 'text/html'));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
