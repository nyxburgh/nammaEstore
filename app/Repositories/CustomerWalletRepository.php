<?php
namespace App\Repositories;

use App\Core\Repository;

class CustomerWalletRepository extends Repository
{
    protected string $table = 'customer_wallets';

    public function getOrCreate(int $userId): array
    {
        $wallet = $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE user_id=?", [$userId]);
        if ($wallet) return $wallet;

        $this->db->insert("INSERT INTO `{$this->t()}` (user_id) VALUES (?)", [$userId]);
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE user_id=?", [$userId]);
    }

    public function credit(int $userId, float $amount, string $referenceType, ?int $referenceId, string $description): void
    {
        $this->db->beginTransaction();
        try {
            $this->getOrCreate($userId);
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance+?, total_added=total_added+? WHERE user_id=?",
                [$amount, $amount, $userId]
            );
            $w = $this->db->fetchOne("SELECT balance FROM `{$this->t()}` WHERE user_id=?", [$userId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('customer_wallet_transactions')}`
                 (user_id, type, amount, balance_after, reference_type, reference_id, description)
                 VALUES (?,'credit',?,?,?,?,?)",
                [$userId, $amount, $w['balance'], $referenceType, $referenceId, $description]
            );
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Debits up to the wallet balance, returns the actual amount debited (never throws for insufficient funds — caller should cap the request first via getOrCreate()['balance']). */
    public function debit(int $userId, float $amount, string $referenceType, ?int $referenceId, string $description): float
    {
        $this->db->beginTransaction();
        try {
            $wallet = $this->getOrCreate($userId);
            $actual = min($amount, (float) $wallet['balance']);
            if ($actual <= 0) {
                $this->db->commit();
                return 0.0;
            }
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance-?, total_used=total_used+? WHERE user_id=?",
                [$actual, $actual, $userId]
            );
            $w = $this->db->fetchOne("SELECT balance FROM `{$this->t()}` WHERE user_id=?", [$userId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('customer_wallet_transactions')}`
                 (user_id, type, amount, balance_after, reference_type, reference_id, description)
                 VALUES (?,'debit',?,?,?,?,?)",
                [$userId, $actual, $w['balance'], $referenceType, $referenceId, $description]
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
        $sql = "SELECT * FROM `{$this->t('customer_wallet_transactions')}` WHERE user_id=? ORDER BY created_at DESC";
        return $this->paginate($sql, [$userId], $page);
    }
}
