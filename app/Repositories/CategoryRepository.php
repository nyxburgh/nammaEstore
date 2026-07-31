<?php
namespace App\Repositories;

use App\Core\Repository;

class CategoryRepository extends Repository
{
    protected string $table = 'categories';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, p.name as parent_name,
                    (SELECT COUNT(*) FROM `{$this->t()}` WHERE parent_id=c.id) as child_count,
                    (SELECT COUNT(*) FROM `{$this->t('products')}` WHERE category_id=c.id) as product_count
             FROM `{$this->t()}` c LEFT JOIN `{$this->t()}` p ON p.id=c.parent_id
             ORDER BY c.parent_id ASC, c.sort_order ASC, c.name ASC"
        );
    }

    public function getFlat(): array
    {
        return $this->db->fetchAll("SELECT id,name,parent_id FROM `{$this->t()}` WHERE is_active=1 ORDER BY name");
    }

    public function getAllActive(): array
    {
        return $this->db->fetchAll("SELECT * FROM `{$this->t()}` WHERE is_active=1 ORDER BY name");
    }

    public function getParents(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM `{$this->t('products')}` WHERE category_id=c.id) as product_count
             FROM `{$this->t()}` c WHERE c.parent_id IS NULL ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    public function getChildrenGroupedByParent(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM `{$this->t('products')}` WHERE category_id=c.id) as product_count
             FROM `{$this->t()}` c WHERE c.parent_id IS NOT NULL ORDER BY c.sort_order ASC, c.name ASC"
        );
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['parent_id']][] = $row;
        }
        return $grouped;
    }

    public function getActiveParents(?int $excludeId = null): array
    {
        if ($excludeId) {
            return $this->db->fetchAll(
                "SELECT * FROM `{$this->t()}` WHERE parent_id IS NULL AND id!=? AND is_active=1 ORDER BY name",
                [$excludeId]
            );
        }
        return $this->db->fetchAll("SELECT * FROM `{$this->t()}` WHERE parent_id IS NULL AND is_active=1 ORDER BY name");
    }

    public function slugExists(string $slug): bool
    {
        return (bool) $this->db->fetchOne("SELECT id FROM `{$this->t()}` WHERE slug=?", [$slug]);
    }

    public function subCategoryCount(int $id): int
    {
        return $this->count("parent_id=?", [$id]);
    }

    public function productCount(int $id): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM `{$this->t('products')}` WHERE category_id=?", [$id]
        )['c'] ?? 0);
    }

    public function toggleStatus(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_active=!is_active WHERE id=?", [$id]);
    }
}
