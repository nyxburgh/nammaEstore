<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Core\Services\ShipmentService;
use App\Repositories\ShipmentRepository;

class ShipmentController extends AdminController
{
    public function index(): void
    {
        Middleware::adminAuth();
        $f = ['status' => $this->input('status', ''), 'search' => $this->input('search', ''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('shipments.index', [
            'title'        => 'Shipments',
            'shipments'    => (new ShipmentRepository())->getForAdmin((int) $this->input('page', 1), $f),
            'filters'      => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function updateTracking(string $id): void
    {
        Middleware::adminAuth(); csrf_check();
        $r = (new ShipmentService())->addTrackingUpdate(
            (int) $id, $this->input('status', ''), $this->input('location') ?: null, $this->input('note') ?: null
        );
        $this->json($r);
    }
}
