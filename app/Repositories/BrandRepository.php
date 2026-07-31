<?php
namespace App\Repositories;

use App\Core\Repository;

class BrandRepository extends Repository
{
    protected string $table = 'brands';

    public function getAll(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['search'])) { $where .= ' AND name LIKE ?'; $params[] = '%' . $f['search'] . '%'; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'name', 'created_at'], 'name');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT b.*, (SELECT COUNT(*) FROM `{$this->t('products')}` WHERE brand_id=b.id) as product_count
                FROM `{$this->t()}` b WHERE $where ORDER BY b.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function getActive(): array
    {
        return $this->db->fetchAll("SELECT * FROM `{$this->t()}` WHERE is_active=1 ORDER BY name");
    }

    public function slugExists(string $slug): bool
    {
        return (bool) $this->db->fetchOne("SELECT id FROM `{$this->t()}` WHERE slug=?", [$slug]);
    }

    public function toggleStatus(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_active=!is_active WHERE id=?", [$id]);
    }
}
