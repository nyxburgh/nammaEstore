<?php
namespace App\Admin\Controllers;
use App\Core\{Middleware, Auth};
use App\Admin\Services\{ProductService, CategoryService, SellerService, BrandService};

class ProductController extends AdminController
{
    private ProductService $products;
    private CategoryService $categories;
    private SellerService $sellers;
    private BrandService $brands;

    public function __construct() {
        parent::__construct();
        $this->products   = new ProductService();
        $this->categories = new CategoryService();
        $this->sellers    = new SellerService();
        $this->brands     = new BrandService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $f = [
            'search'      => $this->input('search'),
            'status'      => $this->input('status'),
            'category_id' => $this->input('category_id'),
            'seller_id'   => $this->input('seller_id'),
            'sort'        => $this->input('sort'),
            'dir'         => $this->input('dir'),
        ];
        $this->view('products.index', [
            'title'        => 'Products',
            'products'     => $this->products->list((int)$this->input('page', 1), $f),
            'stats'        => $this->products->stats(),
            'categories'   => $this->categories->flatOptions(),
            'brands'       => $this->brands->list(1, [])['data'] ?? [],
            'filters'      => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function create(): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        $this->view('products.form', [
            'title'        => 'Add Product',
            'product'      => null,
            'categories'   => $this->categories->flatOptions(),
            'brands'       => $this->brands->list(1, [])['data'] ?? [],
            'sellers'      => $this->sellers->options(),
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function store(): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        csrf_check();
        if (empty($_POST['name']))      { $this->setFlash('error', 'Product name is required.'); $this->redirectWithInput(ADMIN_URL . '/products/create'); return; }
        if (empty($_POST['seller_id'])) { $this->setFlash('error', 'Seller is required.');        $this->redirectWithInput(ADMIN_URL . '/products/create'); return; }
        if (empty($_POST['price']))     { $this->setFlash('error', 'Price is required.');         $this->redirectWithInput(ADMIN_URL . '/products/create'); return; }

        $id = $this->products->save(null, $_POST, $_FILES);
        $this->setFlash('success', 'Product created successfully.');
        $this->redirect(ADMIN_URL . '/products/' . $id);
    }

    public function show(string $id): void
    {
        Middleware::adminAuth();
        $product = $this->products->find((int)$id);
        if (!$product) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect(ADMIN_URL . '/products');
            return;
        }
        $this->view('products.show', [
            'title'        => e($product['name']),
            'product'      => $product,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function edit(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        $product = $this->products->find((int)$id);
        if (!$product) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect(ADMIN_URL . '/products');
            return;
        }
        $this->view('products.form', [
            'title'        => 'Edit: ' . e($product['name']),
            'product'      => $product,
            'categories'   => $this->categories->flatOptions(),
            'brands'       => $this->brands->list(1, [])['data'] ?? [],
            'sellers'      => $this->sellers->options(),
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function update(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        csrf_check();
        $product = $this->products->find((int)$id);
        if (!$product) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect(ADMIN_URL . '/products');
            return;
        }
        $this->products->save((int)$id, $_POST, $_FILES);
        $this->setFlash('success', 'Product updated successfully.');
        $this->redirect(ADMIN_URL . '/products/' . $id);
    }

    public function updateStatus(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        csrf_check();
        $status = $this->input('status', '');
        $ok = $this->products->updateStatus((int)$id, $status, Auth::adminId());
        $this->json(['success' => $ok]);
    }

    public function delete(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'delete');
        csrf_check();
        $this->products->delete((int)$id);
        $this->json(['success' => true]);
    }
}
