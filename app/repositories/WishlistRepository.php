<?php
namespace App\Repositories;

use App\Core\Repository;

class WishlistRepository extends Repository
{
    protected string $table = 'wishlists';

    public function getByUser(int $userId, int $page = 1): array
    {
        $sql = "SELECT w.*, p.name, p.slug, p.price, p.sale_price, p.status,
                       (SELECT image_path FROM `{$this->t('product_images')}` WHERE product_id=p.id AND is_primary=1 LIMIT 1) as image,
                       vp.shop_name
                FROM `{$this->t()}` w
                JOIN `{$this->t('products')}` p ON p.id=w.product_id
                LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=p.vendor_id
                WHERE w.user_id=? ORDER BY w.created_at DESC";
        return $this->paginate($sql, [$userId], $page);
    }

    public function toggle(int $userId, int $productId): bool
    {
        $existing = $this->db->fetchOne("SELECT id FROM `{$this->t()}` WHERE user_id=? AND product_id=?", [$userId, $productId]);
        if ($existing) {
            $this->db->execute("DELETE FROM `{$this->t()}` WHERE id=?", [$existing['id']]);
            return false;
        }
        $this->db->insert("INSERT INTO `{$this->t()}` (user_id,product_id) VALUES (?,?)", [$userId, $productId]);
        return true;
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        return (bool) $this->db->fetchOne("SELECT id FROM `{$this->t()}` WHERE user_id=? AND product_id=?", [$userId, $productId]);
    }

    public function getUserWishlistIds(int $userId): array
    {
        $rows = $this->db->fetchAll("SELECT product_id FROM `{$this->t()}` WHERE user_id=?", [$userId]);
        return array_column($rows, 'product_id');
    }
}
