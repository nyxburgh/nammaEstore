<?php
namespace App\Repositories;

use App\Core\Repository;

class PaymentRepository extends Repository
{
    protected string $table = 'payments';

    public function getPayments(int $page, array $f): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['status'])) { $where .= " AND pay.status=?"; $params[] = $f['status']; }
        if (!empty($f['method'])) { $where .= " AND o.payment_method=?"; $params[] = $f['method']; }
        if (!empty($f['search'])) { $where .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)"; $s = '%' . $f['search'] . '%'; $params[] = $s; $params[] = $s; $params[] = $s; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'amount', 'status', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT pay.*, o.order_number, o.payment_method, u.name as customer_name, u.email as customer_email
                FROM `{$this->t()}` pay
                JOIN `{$this->t('orders')}` o ON o.id=pay.order_id
                JOIN `{$this->t('users')}` u ON u.id=pay.user_id
                WHERE $where ORDER BY pay.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function getTotals(): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status='success' THEN amount ELSE 0 END) as collected,
                    SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) as pending_amt,
                    SUM(CASE WHEN status='failed'  THEN 1 ELSE 0 END) as failed_count
             FROM `{$this->t()}`"
        ) ?? [];
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status=?, paid_at=IF(?='success',NOW(),paid_at) WHERE id=?",
            [$status, $status, $id]
        );
    }
}
