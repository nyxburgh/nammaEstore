<?php
namespace App\Admin\Services;

use App\Repositories\BannerRepository;

class BannerService
{
    private BannerRepository $banners;

    public function __construct()
    {
        $this->banners = new BannerRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->banners->getAll($page, $filters);
    }

    public function find(int $id): ?array
    {
        return $this->banners->findById($id);
    }

    public function create(array $d, array $files, int $adminId): array
    {
        if (empty($files['image']['name'])) {
            return ['success' => false, 'message' => 'Banner image is required.'];
        }
        $img = uploadFile($files['image'], 'banners');
        if (!$img) {
            return ['success' => false, 'message' => 'Image upload failed.'];
        }
        $this->banners->insert([
            'title'      => $d['title'],
            'image_path' => $img,
            'link_url'   => $d['link_url'] ?? null,
            'position'   => $d['position'],
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'is_active'  => 1,
            'starts_at'  => $d['starts_at'] ?: null,
            'ends_at'    => $d['ends_at'] ?: null,
            'created_by' => $adminId,
        ]);
        (new ActivityService())->log('admin', $adminId, 'create_banner', 'banners', "Banner: {$d['title']}");
        return ['success' => true];
    }

    public function update(int $id, array $d, array $files): void
    {
        $data = [
            'title'      => $d['title'],
            'link_url'   => $d['link_url'] ?? null,
            'position'   => $d['position'],
            'sort_order' => (int) ($d['sort_order'] ?? 0),
            'starts_at'  => $d['starts_at'] ?: null,
            'ends_at'    => $d['ends_at'] ?: null,
        ];
        if (!empty($files['image']['name'])) {
            $img = uploadFile($files['image'], 'banners');
            if ($img) $data['image_path'] = $img;
        }
        $this->banners->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->banners->delete($id);
    }

    public function toggleStatus(int $id): void
    {
        $this->banners->toggleStatus($id);
    }
}
