<?php
namespace App\Repositories;

use App\Core\Repository;

class CouponRepository extends Repository
{
    protected string $table = 'coupons';

    public function findByCode(string $code): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE code=?", [strtoupper($code)]);
    }

    public function getAll(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['search'])) { $where .= ' AND code LIKE ?'; $params[] = '%' . $f['search'] . '%'; }
        if (!empty($f['scope']))  { $where .= ' AND scope=?';     $params[] = $f['scope']; }
        if (isset($f['is_active']) && $f['is_active'] !== '') { $where .= ' AND is_active=?'; $params[] = (int) $f['is_active']; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'code', 'value', 'expires_at', 'used_count', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT * FROM `{$this->t()}` WHERE $where ORDER BY {$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function userUsageCount(int $couponId, int $userId): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM `{$this->t('coupon_usages')}` WHERE coupon_id=? AND user_id=?",
            [$couponId, $userId]
        )['c'] ?? 0);
    }

    public function userHasAnyOrder(int $userId): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT id FROM `{$this->t('orders')}` WHERE user_id=? AND payment_status='paid'", [$userId]
        );
    }

    /** Record redemption and bump the running used_count in one transaction. */
    public function recordUsage(int $couponId, int $userId, int $orderId, float $discountAmount): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->insert(
                "INSERT INTO `{$this->t('coupon_usages')}` (coupon_id,user_id,order_id,discount_amount) VALUES (?,?,?,?)",
                [$couponId, $userId, $orderId, $discountAmount]
            );
            $this->db->execute("UPDATE `{$this->t()}` SET used_count=used_count+1 WHERE id=?", [$couponId]);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function toggleStatus(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_active=!is_active WHERE id=?", [$id]);
    }
}
