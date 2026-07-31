<?php
namespace App\Admin\Services;

use App\Repositories\BrandRepository;

class BrandService
{
    private BrandRepository $brands;

    public function __construct()
    {
        $this->brands = new BrandRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->brands->getAll($page, $filters);
    }

    public function find(int $id): ?array
    {
        return $this->brands->findById($id);
    }

    public function create(array $d, array $files): array
    {
        $name = trim($d['name'] ?? '');
        if (!$name) {
            return ['success' => false, 'message' => 'Name is required.'];
        }
        $slug = slugify($name);
        if ($this->brands->slugExists($slug)) {
            $slug .= '-' . time();
        }
        $logo = !empty($files['logo']['name']) ? uploadFile($files['logo'], 'brands') : null;

        $this->brands->insert([
            'name'      => $name,
            'slug'      => $slug,
            'logo'      => $logo,
            'is_active' => (int) !empty($d['is_active']),
        ]);
        return ['success' => true];
    }

    public function update(int $id, array $d, array $files): array
    {
        $name = trim($d['name'] ?? '');
        if (!$name) {
            return ['success' => false, 'message' => 'Name is required.'];
        }
        $data = ['name' => $name, 'is_active' => (int) !empty($d['is_active'])];
        if (!empty($files['logo']['name'])) {
            $logo = uploadFile($files['logo'], 'brands');
            if ($logo) $data['logo'] = $logo;
        }
        $this->brands->update($id, $data);
        return ['success' => true];
    }

    public function delete(int $id): void
    {
        $this->brands->delete($id);
    }

    public function toggle(int $id): void
    {
        $this->brands->toggleStatus($id);
    }
}
