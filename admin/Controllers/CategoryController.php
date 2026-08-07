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
        $parentId = (int) $this->input('parent_id', 0);
        $parent   = $parentId ? $this->service->find($parentId) : null;
        $this->view('categories.form', [
            'title'          => $parent ? 'Add Sub Category' : 'Add Category',
            'category'       => null,
            'parentCategory' => $parent,
            'parents'        => $this->service->parentOptions(),
            'sidebarStats'   => $this->sidebarStats(),
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
            $this->redirectWithInput(ADMIN_URL . '/categories/create');
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

        $parent = !empty($cat['parent_id']) ? $this->service->find((int)$cat['parent_id']) : null;
        $this->view('categories.form', [
            'title'          => $parent ? 'Edit Sub Category' : 'Edit Category',
            'category'       => $cat,
            'parentCategory' => $parent,
            'parents'        => $this->service->parentOptions((int)$id),
            'sidebarStats'   => $this->sidebarStats(),
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
            $this->redirectWithInput(ADMIN_URL . '/categories/' . $id . '/edit');
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
