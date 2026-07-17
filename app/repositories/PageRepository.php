<?php
namespace App\Repositories;

use App\Core\Repository;

class PageRepository extends Repository
{
    protected string $table = 'pages';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE slug=?", [$slug]);
    }

    /** Active pages in display order (for footer / info center / menus). */
    public function allActive(): array
    {
        return $this->db->fetchAll(
            "SELECT slug, icon, title, short_description FROM `{$this->t()}`
             WHERE is_active=1 ORDER BY sort_order ASC, id ASC"
        );
    }

    public function getAll(int $page = 1, array $f = []): array
    {
        $w = '1=1'; $p = [];
        if (!empty($f['search'])) { $w .= " AND (title LIKE ? OR slug LIKE ?)"; $p[] = '%'.$f['search'].'%'; $p[] = '%'.$f['search'].'%'; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'title', 'slug', 'sort_order', 'updated_at'], 'sort_order');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT id, slug, icon, title, short_description, is_active, sort_order, updated_at
                FROM `{$this->t()}` WHERE $w ORDER BY {$sortCol} {$sortDir}";
        return $this->paginate($sql, $p, $page);
    }

    public function toggleStatus(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_active=!is_active WHERE id=?", [$id]);
    }
}
