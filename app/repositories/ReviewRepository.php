<?php
namespace App\Repositories;

use App\Core\Repository;

class ReviewRepository extends Repository
{
    protected string $table = 'reviews';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function getByProduct(int $productId, int $page = 1): array
    {
        $sql = "SELECT r.*, u.name as user_name, u.avatar
                FROM `{$this->t()}` r
                JOIN `{$this->t('users')}` u ON u.id=r.user_id
                WHERE r.product_id=? AND r.is_approved=1
                ORDER BY r.created_at DESC";
        return $this->paginate($sql, [$productId], $page);
    }

    public function getStats(int $productId): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) as total, COALESCE(AVG(rating),0) as avg_rating,
                    SUM(rating=5) as r5, SUM(rating=4) as r4, SUM(rating=3) as r3,
                    SUM(rating=2) as r2, SUM(rating=1) as r1
             FROM `{$this->t()}` WHERE product_id=? AND is_approved=1",
            [$productId]
        ) ?? [];
    }

    public function getByUser(int $userId, int $page = 1): array
    {
        $sql = "SELECT r.*, p.name as product_name,
                       (SELECT image_path FROM `{$this->t('product_images')}` WHERE product_id=p.id AND is_primary=1 LIMIT 1) as product_image
                FROM `{$this->t()}` r
                JOIN `{$this->t('products')}` p ON p.id=r.product_id
                WHERE r.user_id=? ORDER BY r.created_at DESC";
        return $this->paginate($sql, [$userId], $page);
    }

    public function hasReviewed(int $userId, int $productId): bool
    {
        return (bool) $this->db->fetchOne("SELECT id FROM `{$this->t()}` WHERE user_id=? AND product_id=?", [$userId, $productId]);
    }

    public function create(array $data): int
    {
        $cols = implode(',', array_keys($data));
        $ph   = implode(',', array_fill(0, count($data), '?'));
        return $this->db->insert("INSERT INTO `{$this->t()}` ($cols) VALUES ($ph)", array_values($data));
    }

    // ── Admin moderation ──────────────────────────────────────
    public function getForModeration(int $page, array $f): array
    {
        $where = '1=1'; $params = [];
        if (($f['status'] ?? '') === 'pending')       { $where .= " AND r.is_approved=0 AND r.is_flagged=0"; }
        elseif (($f['status'] ?? '') === 'approved')  { $where .= " AND r.is_approved=1"; }
        elseif (($f['status'] ?? '') === 'flagged')   { $where .= " AND r.is_flagged=1"; }
        if (!empty($f['search'])) { $where .= " AND (p.name LIKE ? OR u.name LIKE ?)"; $s = '%' . $f['search'] . '%'; $params[] = $s; $params[] = $s; }
        if (!empty($f['rating'])) { $where .= " AND r.rating=?"; $params[] = (int) $f['rating']; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'rating', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT r.*, p.name as product_name, p.slug as product_slug,
                       u.name as customer_name, u.email as customer_email, vp.shop_name
                FROM `{$this->t()}` r
                JOIN `{$this->t('products')}` p ON p.id=r.product_id
                JOIN `{$this->t('users')}` u ON u.id=r.user_id
                LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=p.seller_id
                WHERE $where ORDER BY r.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function getModerationStats(): array
    {
        return [
            'total'    => $this->count(),
            'pending'  => $this->count("is_approved=0 AND is_flagged=0"),
            'approved' => $this->count("is_approved=1"),
            'flagged'  => $this->count("is_flagged=1"),
        ];
    }

    public function approve(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_approved=1, is_flagged=0 WHERE id=?", [$id]);
    }

    public function reject(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_flagged=1, is_approved=0 WHERE id=?", [$id]);
    }
}
