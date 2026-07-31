<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\DisputeService;

class DisputeController extends AdminController
{
    private DisputeService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new DisputeService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $f = ['status' => $this->input('status', 'open'), 'search' => $this->input('search',''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('disputes.index', [
            'title'        => 'Disputes',
            'disputes'     => $this->service->queue((int) $this->input('page', 1), $f),
            'filters'      => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function resolve(string $id): void
    {
        Middleware::adminAuth(); csrf_check();
        $this->service->resolve((int) $id, $this->input('note', ''));
        $this->json(['success' => true]);
    }
}
