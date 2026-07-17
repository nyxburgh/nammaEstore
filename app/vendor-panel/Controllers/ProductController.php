<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\VendorPanel\Services\VendorProductService;
use App\Repositories\CategoryRepository;

class ProductController extends VendorController
{
    private function svc(): VendorProductService { return new VendorProductService(); }

    private function cats(): array
    {
        return (new CategoryRepository())->getAllActive();
    }

    public function index(): void
    {
        Middleware::vendorAuth();
        $f = [
            'q'        => $this->input('q', ''),
            'status'   => $this->input('status', 'all'),
            'category' => $this->input('category'),
        ];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'      => 'My Products',
            'section'    => 'products',
            'products'   => $this->svc()->getAll(Auth::vendorId(), (int)$this->input('page', 1), $f),
            'categories' => $this->cats(),
            'filters'    => $f,
        ]));
    }

    public function addForm(): void
    {
        Middleware::vendorAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'      => 'Add Product',
            'section'    => 'add-product',
            'categories' => $this->cats(),
            'product'    => null,
        ]));
    }

    public function store(): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $r = $this->svc()->create(Auth::vendorId(), $_POST, $_FILES);
        if (!$r['success']) {
            $this->setFlash('error', $r['message']);
            $this->redirect(VENDOR_URL . '/products/add');
            return;
        }
        $this->setFlash('success', 'Product published successfully!');
        $this->redirect(VENDOR_URL . '/products');
    }

    public function editForm(string $id): void
    {
        Middleware::vendorAuth();
        $p = $this->svc()->findById((int)$id, Auth::vendorId());
        if (!$p) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect(VENDOR_URL . '/products');
            return;
        }
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'      => 'Edit Product',
            'section'    => 'add-product',
            'categories' => $this->cats(),
            'product'    => $p,
        ]));
    }

    public function update(string $id): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $r = $this->svc()->update((int)$id, Auth::vendorId(), $_POST, $_FILES);
        if (!$r['success']) {
            $this->setFlash('error', $r['message']);
            $this->redirect(VENDOR_URL . '/products/' . $id . '/edit');
            return;
        }
        $this->setFlash('success', 'Product updated successfully!');
        $this->redirect(VENDOR_URL . '/products');
    }

    public function delete(string $id): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $this->svc()->delete((int)$id, Auth::vendorId());
        $this->json(['success' => true]);
    }

    public function toggle(string $id): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $new = $this->svc()->toggle((int)$id, Auth::vendorId());
        $this->json(['success' => true, 'status' => $new]);
    }
}
