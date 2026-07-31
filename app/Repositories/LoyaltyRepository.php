<?php
namespace App\Repositories;

use App\Core\Repository;

class LoyaltyRepository extends Repository
{
    protected string $table = 'loyalty_points';

    public function getOrCreate(int $userId): array
    {
        $row = $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE user_id=?", [$userId]);
        if ($row) return $row;

        $this->db->insert("INSERT INTO `{$this->t()}` (user_id) VALUES (?)", [$userId]);
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE user_id=?", [$userId]);
    }

    public function credit(int $userId, int $points, ?int $orderId, string $description): void
    {
        $this->db->beginTransaction();
        try {
            $this->getOrCreate($userId);
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance+?, total_earned=total_earned+? WHERE user_id=?",
                [$points, $points, $userId]
            );
            $this->db->insert(
                "INSERT INTO `{$this->t('loyalty_transactions')}` (user_id,type,points,order_id,description) VALUES (?,'earned',?,?,?)",
                [$userId, $points, $orderId, $description]
            );
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Redeems up to the available balance, returns the actual points redeemed. */
    public function redeem(int $userId, int $points, ?int $orderId, string $description): int
    {
        $this->db->beginTransaction();
        try {
            $row = $this->getOrCreate($userId);
            $actual = min($points, (int) $row['balance']);
            if ($actual <= 0) { $this->db->commit(); return 0; }

            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance-?, total_redeemed=total_redeemed+? WHERE user_id=?",
                [$actual, $actual, $userId]
            );
            $this->db->insert(
                "INSERT INTO `{$this->t('loyalty_transactions')}` (user_id,type,points,order_id,description) VALUES (?,'redeemed',?,?,?)",
                [$userId, -$actual, $orderId, $description]
            );
            $this->db->commit();
            return $actual;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTransactions(int $userId, int $page = 1): array
    {
        $sql = "SELECT * FROM `{$this->t('loyalty_transactions')}` WHERE user_id=? ORDER BY created_at DESC";
        return $this->paginate($sql, [$userId], $page);
    }
}
