<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\PaymentService;

class PaymentController extends AdminController
{
    private PaymentService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new PaymentService();
    }

    public function index(): void
    {
        Middleware::adminAuth(); Middleware::can('payments');
        $f = ['status' => $this->input('status',''), 'search' => $this->input('search',''), 'method' => $this->input('method',''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];

        $this->view('payments.index', [
            'title'    => 'Payments',
            'payments' => $this->service->list((int)$this->input('page', 1), $f),
            'filters'  => $f,
            'totals'   => $this->service->totals(),
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function updateStatus(string $id): void
    {
        Middleware::adminAuth(); Middleware::can('payments'); csrf_check();
        $status = $this->input('status','');
        $ok = $this->service->updateStatus((int)$id, $status);
        $this->json($ok ? ['success'=>true] : ['success'=>false,'message'=>'Invalid status.']);
    }
}
