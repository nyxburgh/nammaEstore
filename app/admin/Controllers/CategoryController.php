<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\CategoryService;

class CategoryController extends AdminController
{
    private CategoryService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new CategoryService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $g = $this->service->grouped();
        $this->view('categories.index', [
            'title'        => 'Categories',
            'parents'      => $g['parents'],
            'children'     => $g['children'],
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function create(): void
    {
        Middleware::adminAuth();
        Middleware::can('products');
        $this->view('categories.form', [
            'title'        => 'Add Category',
            'category'     => null,
            'parents'      => $this->service->parentOptions(),
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function store(): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        csrf_check();

        $r = $this->service->create($this->inputs());
        if (!$r['success']) {
            $this->setFlash('error', $r['message']);
            $this->redirect(ADMIN_URL . '/categories/create');
            return;
        }
        $this->setFlash('success', 'Category created successfully.');
        $this->redirect(ADMIN_URL . '/categories');
    }

    public function edit(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products');

        $cat = $this->service->find((int)$id);
        if (!$cat) {
            $this->setFlash('error', 'Category not found.');
            $this->redirect(ADMIN_URL . '/categories');
            return;
        }

        $this->view('categories.form', [
            'title'        => 'Edit Category',
            'category'     => $cat,
            'parents'      => $this->service->parentOptions((int)$id),
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function update(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        csrf_check();

        $r = $this->service->update((int)$id, $this->inputs());
        if (!$r['success']) {
            $this->setFlash('error', $r['message']);
            $this->redirect(ADMIN_URL . '/categories/' . $id . '/edit');
            return;
        }
        $this->setFlash('success', 'Category updated successfully.');
        $this->redirect(ADMIN_URL . '/categories');
    }

    public function delete(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'delete');
        csrf_check();

        $r = $this->service->delete((int)$id);
        $this->json($r);
    }

    public function toggle(string $id): void
    {
        Middleware::adminAuth();
        Middleware::can('products', 'edit');
        csrf_check();

        $this->service->toggle((int)$id);
        $this->json(['success' => true]);
    }
}
