<?php
namespace App\Repositories;

use App\Core\Repository;

class SellerWalletRepository extends Repository
{
    protected string $table = 'seller_wallets';

    public function getOrCreate(int $sellerId): array
    {
        $wallet = $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE seller_id=?", [$sellerId]);
        if ($wallet) return $wallet;

        $this->db->insert("INSERT INTO `{$this->t()}` (seller_id) VALUES (?)", [$sellerId]);
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE seller_id=?", [$sellerId]);
    }

    /**
     * Credit the wallet and write the matching ledger row atomically.
     */
    public function credit(int $sellerId, float $amount, string $referenceType, ?int $referenceId, string $description): void
    {
        $this->db->beginTransaction();
        try {
            $this->getOrCreate($sellerId);
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance+?, total_earned=total_earned+? WHERE seller_id=?",
                [$amount, $amount, $sellerId]
            );
            $wallet = $this->db->fetchOne("SELECT balance FROM `{$this->t()}` WHERE seller_id=?", [$sellerId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('seller_wallet_transactions')}`
                 (seller_id, type, amount, balance_after, reference_type, reference_id, description)
                 VALUES (?,'credit',?,?,?,?,?)",
                [$sellerId, $amount, $wallet['balance'], $referenceType, $referenceId, $description]
            );
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Debit the wallet and write the matching ledger row atomically.
     * Throws if the wallet doesn't have sufficient balance.
     */
    public function debit(int $sellerId, float $amount, string $referenceType, ?int $referenceId, string $description): void
    {
        $this->db->beginTransaction();
        try {
            $wallet = $this->getOrCreate($sellerId);
            if ((float) $wallet['balance'] < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance-?, total_withdrawn=total_withdrawn+? WHERE seller_id=?",
                [$amount, $amount, $sellerId]
            );
            $updated = $this->db->fetchOne("SELECT balance FROM `{$this->t()}` WHERE seller_id=?", [$sellerId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('seller_wallet_transactions')}`
                 (seller_id, type, amount, balance_after, reference_type, reference_id, description)
                 VALUES (?,'debit',?,?,?,?,?)",
                [$sellerId, $amount, $updated['balance'], $referenceType, $referenceId, $description]
            );
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTransactions(int $sellerId, int $page = 1): array
    {
        $sql = "SELECT * FROM `{$this->t('seller_wallet_transactions')}` WHERE seller_id=? ORDER BY created_at DESC";
        return $this->paginate($sql, [$sellerId], $page);
    }
}
