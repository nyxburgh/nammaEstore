<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\{GstReportService, InvoiceService};
use App\Repositories\InvoiceRepository;
use App\Core\Services\InvoicePdfService;

class InvoiceController extends AdminController
{
    private GstReportService $reports;

    public function __construct() {
        parent::__construct();
        $this->reports = new GstReportService();
    }

    public function index(): void
    {
        Middleware::can('reports');
        $f = ['search' => $this->input('search',''), 'date_from' => $this->input('date_from', date('Y-m-01')), 'date_to' => $this->input('date_to', date('Y-m-d')), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('invoices.index', [
            'title'    => 'GST Invoices',
            'invoices' => $this->reports->list((int) $this->input('page', 1), $f),
            'summary'  => $this->reports->summary($f['date_from'], $f['date_to']),
            'filters'  => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function download(string $id): void
    {
        Middleware::can('reports');
        $invoice = (new InvoiceRepository())->findWithItems((int) $id);
        if (!$invoice) { http_response_code(404); echo 'Invoice not found.'; return; }

        $path = (new InvoicePdfService())->render((int) $id);
        if (!$path) { http_response_code(500); echo 'Could not generate invoice.'; return; }

        $fullPath = UPLOAD_PATH . '/' . $path;
        header('Content-Type: ' . (str_ends_with($path, '.pdf') ? 'application/pdf' : 'text/html'));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function export(): void
    {
        Middleware::can('reports');
        $from = $this->input('date_from', date('Y-m-01'));
        $to   = $this->input('date_to', date('Y-m-d'));
        $csv  = $this->reports->exportCsv($from, $to);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="gst-export-' . $from . '_to_' . $to . '.csv"');
        header('Content-Length: ' . strlen($csv));
        echo $csv;
        exit;
    }

    /** Manually (re)generate invoices for an order — useful if an order was placed before this phase shipped. */
    public function regenerate(string $orderId): void
    {
        Middleware::superAdmin(); csrf_check();
        $results = (new InvoiceService())->generateForOrder((int) $orderId);
        $this->json(['success' => true, 'results' => $results]);
    }
}
