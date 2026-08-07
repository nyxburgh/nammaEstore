<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\BrandService;

class BrandController extends AdminController
{
    private BrandService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new BrandService();
    }

    public function index(): void
    {
        Middleware::can('brands');
        $f = ['search' => $this->input('search', ''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('brands.index', [
            'title'   => 'Brands',
            'brands'  => $this->service->list((int) $this->input('page', 1), $f),
            'filters' => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function create(): void { Middleware::can('brands', 'create'); $this->view('brands.form', ['title' => 'Add Brand', 'brand' => null]); }

    public function store(): void
    {
        Middleware::can('brands', 'create'); csrf_check();
        $r = $this->service->create($this->inputs(), $_FILES);
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->backWithInput(); return; }
        $this->setFlash('success', 'Brand created.');
        $this->redirect(ADMIN_URL . '/brands');
    }

    public function edit(string $id): void
    {
        Middleware::can('brands', 'edit');
        $brand = $this->service->find((int) $id);
        if (!$brand) { $this->redirect(ADMIN_URL . '/brands'); return; }
        $this->view('brands.form', ['title' => 'Edit Brand', 'brand' => $brand]);
    }

    public function update(string $id): void
    {
        Middleware::can('brands', 'edit'); csrf_check();
        $r = $this->service->update((int) $id, $this->inputs(), $_FILES);
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->backWithInput(); return; }
        $this->setFlash('success', 'Brand updated.');
        $this->redirect(ADMIN_URL . '/brands');
    }

    public function delete(string $id): void
    {
        Middleware::can('brands', 'delete'); csrf_check();
        $this->service->delete((int) $id);
        $this->json(['success' => true]);
    }

    public function toggle(string $id): void
    {
        Middleware::can('brands', 'edit'); csrf_check();
        $this->service->toggle((int) $id);
        $this->json(['success' => true]);
    }
}
