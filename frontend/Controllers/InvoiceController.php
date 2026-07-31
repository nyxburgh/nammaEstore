<?php
namespace App\Frontend\Controllers;
use App\Core\{Auth, Middleware};
use App\Repositories\InvoiceRepository;
use App\Core\Services\InvoicePdfService;

class InvoiceController extends FrontendController
{
    public function download(string $id): void
    {
        Middleware::userAuth();
        $invoice = (new InvoiceRepository())->findWithItems((int) $id);
        if (!$invoice || (int) $invoice['user_id'] !== Auth::userId()) {
            http_response_code(404);
            echo 'Invoice not found.';
            return;
        }

        $path = (new InvoicePdfService())->render((int) $id);
        if (!$path) { http_response_code(500); echo 'Could not generate invoice.'; return; }

        $fullPath = UPLOAD_PATH . '/' . $path;
        $isPdf = str_ends_with($path, '.pdf');
        header('Content-Type: ' . ($isPdf ? 'application/pdf' : 'text/html'));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function email(string $id): void
    {
        csrf_check();
        Middleware::userAuth();
        $invoice = (new InvoiceRepository())->findWithItems((int) $id);
        if (!$invoice || (int) $invoice['user_id'] !== Auth::userId()) {
            $this->json(['success' => false, 'message' => 'Invoice not found.']);
            return;
        }
        $sent = (new \App\Core\Services\InvoicePdfService())->emailInvoice((int) $id);
        $this->json($sent
            ? ['success' => true, 'message' => 'Invoice emailed to your registered address.']
            : ['success' => false, 'message' => 'Could not send email right now. Please try downloading instead.']
        );
    }
}
