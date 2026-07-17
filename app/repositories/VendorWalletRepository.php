<?php
namespace App\Repositories;

use App\Core\Repository;

class VendorWalletRepository extends Repository
{
    protected string $table = 'vendor_wallets';

    public function getOrCreate(int $vendorId): array
    {
        $wallet = $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE vendor_id=?", [$vendorId]);
        if ($wallet) return $wallet;

        $this->db->insert("INSERT INTO `{$this->t()}` (vendor_id) VALUES (?)", [$vendorId]);
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE vendor_id=?", [$vendorId]);
    }

    /**
     * Credit the wallet and write the matching ledger row atomically.
     */
    public function credit(int $vendorId, float $amount, string $referenceType, ?int $referenceId, string $description): void
    {
        $this->db->beginTransaction();
        try {
            $this->getOrCreate($vendorId);
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance+?, total_earned=total_earned+? WHERE vendor_id=?",
                [$amount, $amount, $vendorId]
            );
            $wallet = $this->db->fetchOne("SELECT balance FROM `{$this->t()}` WHERE vendor_id=?", [$vendorId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('vendor_wallet_transactions')}`
                 (vendor_id, type, amount, balance_after, reference_type, reference_id, description)
                 VALUES (?,'credit',?,?,?,?,?)",
                [$vendorId, $amount, $wallet['balance'], $referenceType, $referenceId, $description]
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
    public function debit(int $vendorId, float $amount, string $referenceType, ?int $referenceId, string $description): void
    {
        $this->db->beginTransaction();
        try {
            $wallet = $this->getOrCreate($vendorId);
            if ((float) $wallet['balance'] < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }
            $this->db->execute(
                "UPDATE `{$this->t()}` SET balance=balance-?, total_withdrawn=total_withdrawn+? WHERE vendor_id=?",
                [$amount, $amount, $vendorId]
            );
            $updated = $this->db->fetchOne("SELECT balance FROM `{$this->t()}` WHERE vendor_id=?", [$vendorId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('vendor_wallet_transactions')}`
                 (vendor_id, type, amount, balance_after, reference_type, reference_id, description)
                 VALUES (?,'debit',?,?,?,?,?)",
                [$vendorId, $amount, $updated['balance'], $referenceType, $referenceId, $description]
            );
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTransactions(int $vendorId, int $page = 1): array
    {
        $sql = "SELECT * FROM `{$this->t('vendor_wallet_transactions')}` WHERE vendor_id=? ORDER BY created_at DESC";
        return $this->paginate($sql, [$vendorId], $page);
    }
}
