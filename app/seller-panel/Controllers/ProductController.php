<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\SellerPanel\Services\SellerProductService;
use App\Repositories\CategoryRepository;

class ProductController extends SellerController
{
    private function svc(): SellerProductService { return new SellerProductService(); }

    private function cats(): array
    {
        return (new CategoryRepository())->getAllActive();
    }

    public function index(): void
    {
        Middleware::sellerAuth();
        $f = [
            'q'        => $this->input('q', ''),
            'status'   => $this->input('status', 'all'),
            'category' => $this->input('category'),
        ];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'      => 'My Products',
            'section'    => 'products',
            'products'   => $this->svc()->getAll(Auth::sellerId(), (int)$this->input('page', 1), $f),
            'categories' => $this->cats(),
            'filters'    => $f,
        ]));
    }

    public function addForm(): void
    {
        Middleware::sellerAuth();
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
        Middleware::sellerAuth();
        $r = $this->svc()->create(Auth::sellerId(), $_POST, $_FILES);
        if (!$r['success']) {
            $this->setFlash('error', $r['message']);
            $this->redirect(SELLER_URL . '/products/add');
            return;
        }
        $this->setFlash('success', 'Product published successfully!');
        $this->redirect(SELLER_URL . '/products');
    }

    public function editForm(string $id): void
    {
        Middleware::sellerAuth();
        $p = $this->svc()->findById((int)$id, Auth::sellerId());
        if (!$p) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect(SELLER_URL . '/products');
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
        Middleware::sellerAuth();
        $r = $this->svc()->update((int)$id, Auth::sellerId(), $_POST, $_FILES);
        if (!$r['success']) {
            $this->setFlash('error', $r['message']);
            $this->redirect(SELLER_URL . '/products/' . $id . '/edit');
            return;
        }
        $this->setFlash('success', 'Product updated successfully!');
        $this->redirect(SELLER_URL . '/products');
    }

    public function delete(string $id): void
    {
        csrf_check();
        Middleware::sellerAuth();
        $this->svc()->delete((int)$id, Auth::sellerId());
        $this->json(['success' => true]);
    }

    public function toggle(string $id): void
    {
        csrf_check();
        Middleware::sellerAuth();
        $new = $this->svc()->toggle((int)$id, Auth::sellerId());
        $this->json(['success' => true, 'status' => $new]);
    }
}
