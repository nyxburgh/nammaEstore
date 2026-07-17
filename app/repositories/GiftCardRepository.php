<?php
namespace App\Repositories;

use App\Core\Repository;

class GiftCardRepository extends Repository
{
    protected string $table = 'gift_cards';

    public function findByCode(string $code): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE code=?", [strtoupper($code)]);
    }

    public function logIssue(int $cardId, float $amount): void
    {
        $this->db->insert(
            "INSERT INTO `{$this->t('gift_card_transactions')}` (gift_card_id,type,amount,balance_after) VALUES (?,'issue',?,?)",
            [$cardId, $amount, $amount]
        );
    }

    public function getAll(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['search'])) { $where .= ' AND code LIKE ?'; $params[] = '%' . $f['search'] . '%'; }
        if (!empty($f['type']))   { $where .= ' AND type=?';      $params[] = $f['type']; }
        if (!empty($f['status'])) { $where .= ' AND status=?';    $params[] = $f['status']; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'code', 'current_balance', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT gc.*, u.name as vendor_name FROM `{$this->t()}` gc
                LEFT JOIN `{$this->t('users')}` u ON u.id=gc.vendor_id
                WHERE $where ORDER BY gc.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    /**
     * Redeem an amount off the card and log the transaction atomically.
     * Returns the actual amount redeemed (capped at current balance).
     */
    public function redeem(int $cardId, float $amount, ?int $orderId): float
    {
        $this->db->beginTransaction();
        try {
            $card = $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=? FOR UPDATE", [$cardId]);
            $actual = min($amount, (float) $card['current_balance']);
            $newBalance = round((float) $card['current_balance'] - $actual, 2);
            $status = $newBalance <= 0 ? 'redeemed' : 'active';

            $this->db->execute(
                "UPDATE `{$this->t()}` SET current_balance=?, status=? WHERE id=?",
                [$newBalance, $status, $cardId]
            );
            $this->db->insert(
                "INSERT INTO `{$this->t('gift_card_transactions')}` (gift_card_id,type,amount,order_id,balance_after) VALUES (?,'redeem',?,?,?)",
                [$cardId, $actual, $orderId, $newBalance]
            );
            $this->db->commit();
            return $actual;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTransactions(int $cardId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->t('gift_card_transactions')}` WHERE gift_card_id=? ORDER BY created_at DESC",
            [$cardId]
        );
    }
}
