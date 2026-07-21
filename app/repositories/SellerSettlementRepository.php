<?php
namespace App\Repositories;

use App\Core\Repository;

class SellerSettlementRepository extends Repository
{
    protected string $table = 'seller_settlements';

    public function findBySplitId(int $splitId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE order_seller_split_id=?", [$splitId]);
    }

    public function upsert(array $data): int
    {
        $existing = $this->findBySplitId($data['order_seller_split_id']);
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        return $this->insert($data);
    }

    public function markCredited(int $id): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status='credited', credited_at=NOW() WHERE id=?",
            [$id]
        );
    }

    public function markHold(int $id, string $reason): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status='on_hold', hold_reason=? WHERE id=?",
            [$reason, $id]
        );
    }

    /**
     * Splits that are eligible (return window closed, no dispute, etc.)
     * but haven't been credited to the wallet yet.
     */
    public function getUncreditedEligible(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->t()}` WHERE status='eligible' ORDER BY eligible_at ASC"
        );
    }

    public function getForSeller(int $sellerId, int $page = 1, array $f = []): array
    {
        $where = 'seller_id=?'; $params = [$sellerId];
        if (!empty($f['status'])) { $where .= ' AND status=?'; $params[] = $f['status']; }

        $sql = "SELECT s.*, o.order_number FROM `{$this->t()}` s
                JOIN `{$this->t('orders')}` o ON o.id=s.order_id
                WHERE $where ORDER BY s.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function getPendingSummary(int $sellerId): array
    {
        return $this->db->fetchOne(
            "SELECT COALESCE(SUM(CASE WHEN status='not_eligible' THEN net_payable ELSE 0 END),0) as pipeline,
                    COALESCE(SUM(CASE WHEN status='eligible' THEN net_payable ELSE 0 END),0) as awaiting_credit,
                    COALESCE(SUM(CASE WHEN status='on_hold' THEN net_payable ELSE 0 END),0) as on_hold
             FROM `{$this->t()}` WHERE seller_id=?",
            [$sellerId]
        ) ?? ['pipeline' => 0, 'awaiting_credit' => 0, 'on_hold' => 0];
    }
}
