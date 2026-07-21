<?php
namespace App\Repositories;

use App\Core\Repository;

class SellerWithdrawalRepository extends Repository
{
    protected string $table = 'seller_withdrawals';

    public function getForSeller(int $sellerId, int $page = 1): array
    {
        $sql = "SELECT * FROM `{$this->t()}` WHERE seller_id=? ORDER BY requested_at DESC";
        return $this->paginate($sql, [$sellerId], $page);
    }

    public function getQueue(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['status'])) { $where .= ' AND w.status=?'; $params[] = $f['status']; }

        $sql = "SELECT w.*, u.name as seller_name, vp.shop_name
                FROM `{$this->t()}` w
                JOIN `{$this->t('users')}` u ON u.id=w.seller_id
                LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=w.seller_id
                WHERE $where ORDER BY w.requested_at ASC";
        return $this->paginate($sql, $params, $page);
    }

    public function markProcessed(int $id, string $status, int $adminId, string $note = ''): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status=?, admin_note=?, processed_at=NOW(), processed_by=? WHERE id=?",
            [$status, $note, $adminId, $id]
        );
    }

    public function pendingTotalForSeller(int $sellerId): float
    {
        return (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(amount),0) t FROM `{$this->t()}` WHERE seller_id=? AND status='pending'",
            [$sellerId]
        )['t'] ?? 0);
    }
}
