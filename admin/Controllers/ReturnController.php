<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\ReturnService;

class ReturnController extends AdminController
{
    private ReturnService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new ReturnService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $f = ['status' => $this->input('status', ''), 'type' => $this->input('type', ''), 'search' => $this->input('search',''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('returns.index', [
            'title'        => 'Returns & Replacements',
            'returns'      => $this->service->queue((int) $this->input('page', 1), $f),
            'filters'      => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function approve(string $id): void
    {
        Middleware::adminAuth(); csrf_check();
        $this->service->approve((int) $id, Auth::adminId());
        $this->json(['success' => true]);
    }

    public function reject(string $id): void
    {
        Middleware::adminAuth(); csrf_check();
        $this->service->reject((int) $id, Auth::adminId());
        $this->json(['success' => true]);
    }

    public function markRefunded(string $id): void
    {
        Middleware::adminAuth(); csrf_check();
        $this->service->markRefunded((int) $id, Auth::adminId(), (float) $this->input('refund_amount', 0));
        $this->json(['success' => true]);
    }
}
