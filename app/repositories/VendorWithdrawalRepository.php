<?php
namespace App\Repositories;

use App\Core\Repository;

class VendorWithdrawalRepository extends Repository
{
    protected string $table = 'vendor_withdrawals';

    public function getForVendor(int $vendorId, int $page = 1): array
    {
        $sql = "SELECT * FROM `{$this->t()}` WHERE vendor_id=? ORDER BY requested_at DESC";
        return $this->paginate($sql, [$vendorId], $page);
    }

    public function getQueue(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['status'])) { $where .= ' AND w.status=?'; $params[] = $f['status']; }

        $sql = "SELECT w.*, u.name as vendor_name, vp.shop_name
                FROM `{$this->t()}` w
                JOIN `{$this->t('users')}` u ON u.id=w.vendor_id
                LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=w.vendor_id
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

    public function pendingTotalForVendor(int $vendorId): float
    {
        return (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(amount),0) t FROM `{$this->t()}` WHERE vendor_id=? AND status='pending'",
            [$vendorId]
        )['t'] ?? 0);
    }
}
