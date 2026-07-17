<?php
namespace App\Admin\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    private CategoryRepository $categories;

    public function __construct()
    {
        $this->categories = new CategoryRepository();
    }

    public function grouped(): array
    {
        return [
            'parents'  => $this->categories->getParents(),
            'children' => $this->categories->getChildrenGroupedByParent(),
        ];
    }

    public function find(int $id): ?array
    {
        return $this->categories->findById($id);
    }

    public function parentOptions(?int $excludeId = null): array
    {
        return $this->categories->getActiveParents($excludeId);
    }

    public function flatOptions(): array
    {
        return $this->categories->getFlat();
    }

    public function create(array $d): array
    {
        $name = trim($d['name'] ?? '');
        if (!$name) {
            return ['success' => false, 'message' => 'Name is required.'];
        }

        $slug = slugify($name);
        if ($this->categories->slugExists($slug)) {
            $slug .= '-' . time();
        }

        $this->categories->insert([
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => $d['parent_id'] ?: null,
            'description' => $d['description'] ?? '',
            'sort_order'  => (int) ($d['sort_order'] ?? 0),
            'is_active'   => (int) !empty($d['is_active']),
        ]);

        return ['success' => true];
    }

    public function update(int $id, array $d): array
    {
        $name = trim($d['name'] ?? '');
        if (!$name) {
            return ['success' => false, 'message' => 'Name is required.'];
        }

        $this->categories->update($id, [
            'name'        => $name,
            'parent_id'   => $d['parent_id'] ?: null,
            'description' => $d['description'] ?? '',
            'sort_order'  => (int) ($d['sort_order'] ?? 0),
            'is_active'   => (int) !empty($d['is_active']),
        ]);

        return ['success' => true];
    }

    public function delete(int $id): array
    {
        $subCount = $this->categories->subCategoryCount($id);
        if ($subCount > 0) {
            return ['success' => false, 'message' => "Cannot delete: {$subCount} subcategory/ies exist. Delete or reassign them first."];
        }

        $prodCount = $this->categories->productCount($id);
        if ($prodCount > 0) {
            return ['success' => false, 'message' => "Cannot delete: {$prodCount} product(s) use this category. Reassign them first."];
        }

        $this->categories->delete($id);
        return ['success' => true];
    }

    public function toggle(int $id): void
    {
        $this->categories->toggleStatus($id);
    }
}
