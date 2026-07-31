<?php
namespace App\Repositories;

use App\Core\Repository;

class ProductRepository extends Repository
{
    protected string $table = 'products';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function getProducts(int $page = 1, array $f = []): array
    {
        $w = '1=1'; $p = [];
        if (!empty($f['seller_id']))   { $w .= " AND p.seller_id=?";   $p[] = $f['seller_id']; }
        if (!empty($f['category_id'])) { $w .= " AND p.category_id=?"; $p[] = $f['category_id']; }
        if (!empty($f['status']))      { $w .= " AND p.status=?";       $p[] = $f['status']; }
        if (!empty($f['search']))      { $w .= " AND (p.name LIKE ? OR p.sku LIKE ?)"; $s = '%' . $f['search'] . '%'; $p[] = $s; $p[] = $s; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'name', 'price', 'stock', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT p.*, c.name as category_name, u.name as seller_name, vp.shop_name,
                    (SELECT image_path FROM `{$this->t('product_images')}` WHERE product_id=p.id AND is_primary=1 LIMIT 1) as primary_image
                FROM `{$this->t()}` p
                LEFT JOIN `{$this->t('categories')}` c ON c.id=p.category_id
                LEFT JOIN `{$this->t('users')}` u ON u.id=p.seller_id
                LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=p.seller_id
                WHERE $w ORDER BY p.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $p, $page);
    }

    public function getProductDetail(int $id): ?array
    {
        $r = $this->db->fetchOne(
            "SELECT p.*, c.name as category_name, u.name as seller_name, vp.shop_name
             FROM `{$this->t()}` p
             LEFT JOIN `{$this->t('categories')}` c ON c.id=p.category_id
             LEFT JOIN `{$this->t('users')}` u ON u.id=p.seller_id
             LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=p.seller_id
             WHERE p.id=?", [$id]);
        if (!$r) return null;
        $r['images']   = $this->db->fetchAll("SELECT * FROM `{$this->t('product_images')}` WHERE product_id=? ORDER BY sort_order", [$id]);
        $r['variants'] = $this->db->fetchAll("SELECT * FROM `{$this->t('product_variants')}` WHERE product_id=? AND is_active=1", [$id]);
        return $r;
    }

    public function getStats(): array
    {
        return [
            'total'     => $this->count(),
            'active'    => $this->count("status='active'"),
            'draft'     => $this->count("status='draft'"),
            'inactive'  => $this->count("status='inactive'"),
            'low_stock' => $this->count("stock<=low_stock_threshold AND status='active'"),
        ];
    }

    public function updateStatus(int $id, string $status, ?int $adminId = null): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status=?, approved_by=?, approved_at=IF(?='active',NOW(),NULL) WHERE id=?",
            [$status, $adminId, $status, $id]
        );
    }

    public function getSlug(int $id): ?string
    {
        $row = $this->db->fetchOne("SELECT slug FROM `{$this->t()}` WHERE id=?", [$id]);
        return $row['slug'] ?? null;
    }

    public function imageCount(int $productId): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM `{$this->t('product_images')}` WHERE product_id=?", [$productId]
        )['c'] ?? 0);
    }

    public function addImage(int $productId, string $path, bool $isPrimary, int $sortOrder): void
    {
        $this->db->insert(
            "INSERT INTO `{$this->t('product_images')}` (product_id, image_path, is_primary, sort_order) VALUES (?,?,?,?)",
            [$productId, $path, (int) $isPrimary, $sortOrder]
        );
    }

    public function clearVariants(int $productId): void
    {
        $this->db->execute("DELETE FROM `{$this->t('product_variants')}` WHERE product_id=?", [$productId]);
    }

    public function addVariant(int $productId, string $name, string $value, float $priceModifier, int $stock): void
    {
        $this->db->insert(
            "INSERT INTO `{$this->t('product_variants')}` (product_id, variant_name, variant_value, price_modifier, stock, is_active) VALUES (?,?,?,?,?,1)",
            [$productId, $name, $value, $priceModifier, $stock]
        );
    }
}
