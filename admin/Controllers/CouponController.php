<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\{CouponService, CategoryService, BrandService, SellerService};
use App\Repositories\ProductRepository;

class CouponController extends AdminController
{
    private CouponService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new CouponService();
    }

    public function index(): void
    {
        Middleware::can('coupons');
        $f = ['search' => $this->input('search',''), 'scope' => $this->input('scope',''), 'is_active' => $this->input('is_active',''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('coupons.index', [
            'title'   => 'Coupons',
            'coupons' => $this->service->list((int) $this->input('page', 1), $f),
            'filters' => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function create(): void
    {
        Middleware::can('coupons', 'create');
        $this->view('coupons.form', [
            'title'      => 'Create Coupon',
            'coupon'     => null,
            'categories' => (new CategoryService())->flatOptions(),
            'brands'     => (new BrandService())->list(1, [])['data'] ?? [],
            'sellers'    => (new SellerService())->options(),
        ]);
    }

    public function store(): void
    {
        Middleware::can('coupons', 'create'); csrf_check();
        $r = $this->service->create($this->inputs(), Auth::adminId());
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->back(); return; }
        $this->setFlash('success', 'Coupon created.');
        $this->redirect(ADMIN_URL . '/coupons');
    }

    public function edit(string $id): void
    {
        Middleware::can('coupons', 'edit');
        $coupon = $this->service->find((int) $id);
        if (!$coupon) { $this->redirect(ADMIN_URL . '/coupons'); return; }
        $this->view('coupons.form', [
            'title'      => 'Edit Coupon',
            'coupon'     => $coupon,
            'categories' => (new CategoryService())->flatOptions(),
            'brands'     => (new BrandService())->list(1, [])['data'] ?? [],
            'sellers'    => (new SellerService())->options(),
        ]);
    }

    public function update(string $id): void
    {
        Middleware::can('coupons', 'edit'); csrf_check();
        $r = $this->service->update((int) $id, $this->inputs());
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->back(); return; }
        $this->setFlash('success', 'Coupon updated.');
        $this->redirect(ADMIN_URL . '/coupons');
    }

    public function delete(string $id): void
    {
        Middleware::can('coupons', 'delete'); csrf_check();
        $this->service->delete((int) $id);
        $this->json(['success' => true]);
    }

    public function toggle(string $id): void
    {
        Middleware::can('coupons', 'edit'); csrf_check();
        $this->service->toggle((int) $id);
        $this->json(['success' => true]);
    }
}
