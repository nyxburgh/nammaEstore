<?php
namespace App\Admin\Services;

use App\Repositories\PageRepository;

class PageService
{
    private PageRepository $pages;

    public function __construct()
    {
        $this->pages = new PageRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->pages->getAll($page, $filters);
    }

    public function find(int $id): ?array
    {
        return $this->pages->findById($id);
    }

    public function create(array $d, int $adminId): array
    {
        $slug = $this->slugify($d['slug'] ?: $d['title']);
        if ($slug === '') {
            return ['success' => false, 'message' => 'A valid slug is required.'];
        }
        if ($this->pages->findBySlug($slug)) {
            return ['success' => false, 'message' => "Slug '{$slug}' is already in use."];
        }
        $this->pages->insert([
            'slug'              => $slug,
            'icon'              => trim($d['icon'] ?? '') ?: 'ℹ️',
            'title'             => trim($d['title']),
            'short_description' => trim($d['short_description'] ?? ''),
            'content'           => $d['content'] ?? '',
            'is_active'         => !empty($d['is_active']) ? 1 : 0,
            'sort_order'        => (int) ($d['sort_order'] ?? 0),
        ]);
        (new ActivityService())->log('admin', $adminId, 'create_page', 'pages', "Page: {$d['title']}");
        return ['success' => true];
    }

    public function update(int $id, array $d, int $adminId): array
    {
        $existing = $this->pages->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Page not found.'];
        }
        $slug = $this->slugify($d['slug'] ?: $d['title']);
        if ($slug === '') {
            return ['success' => false, 'message' => 'A valid slug is required.'];
        }
        $bySlug = $this->pages->findBySlug($slug);
        if ($bySlug && (int) $bySlug['id'] !== $id) {
            return ['success' => false, 'message' => "Slug '{$slug}' is already in use."];
        }
        $this->pages->update($id, [
            'slug'              => $slug,
            'icon'              => trim($d['icon'] ?? '') ?: 'ℹ️',
            'title'             => trim($d['title']),
            'short_description' => trim($d['short_description'] ?? ''),
            'content'           => $d['content'] ?? '',
            'sort_order'        => (int) ($d['sort_order'] ?? 0),
        ]);
        (new ActivityService())->log('admin', $adminId, 'update_page', 'pages', "Page: {$d['title']}");
        return ['success' => true];
    }

    public function delete(int $id): void
    {
        $this->pages->delete($id);
    }

    public function toggleStatus(int $id): void
    {
        $this->pages->toggleStatus($id);
    }

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-');
    }
}
