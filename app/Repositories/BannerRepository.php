<?php
namespace App\Repositories;

use App\Core\Repository;

class BannerRepository extends Repository
{
    protected string $table = 'banners';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function getAll(int $page = 1, array $f = []): array
    {
        $w = '1=1'; $p = [];
        if (!empty($f['position'])) { $w .= " AND b.position=?"; $p[] = $f['position']; }
        if (!empty($f['search']))   { $w .= " AND b.title LIKE ?"; $p[] = '%' . $f['search'] . '%'; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'title', 'position', 'sort_order', 'created_at'], 'sort_order');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT b.*, a.name as created_by_name FROM `{$this->t()}` b LEFT JOIN `{$this->t('admins')}` a ON a.id=b.created_by WHERE $w ORDER BY b.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $p, $page);
    }

    public function toggleStatus(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_active=!is_active WHERE id=?", [$id]);
    }
}
