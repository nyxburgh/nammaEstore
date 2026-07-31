<?php
namespace App\Repositories;

use App\Core\Repository;

class DisputeRepository extends Repository
{
    protected string $table = 'disputes';

    public function hasOpenDispute(int $orderId, int $sellerId): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT id FROM `{$this->t()}` WHERE order_id=? AND seller_id=? AND status='open'",
            [$orderId, $sellerId]
        );
    }

    public function getQueue(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['status'])) { $where .= ' AND d.status=?'; $params[] = $f['status']; }
        if (!empty($f['search'])) { $where .= ' AND (o.order_number LIKE ? OR u.name LIKE ?)'; $s = '%'.$f['search'].'%'; $params[] = $s; $params[] = $s; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'status', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT d.*, o.order_number, u.name as seller_name
                FROM `{$this->t()}` d
                JOIN `{$this->t('orders')}` o ON o.id=d.order_id
                JOIN `{$this->t('users')}` u ON u.id=d.seller_id
                WHERE $where ORDER BY d.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function resolve(int $id, string $note): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status='resolved', resolution_note=?, resolved_at=NOW() WHERE id=?",
            [$note, $id]
        );
    }
}
