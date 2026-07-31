<?php
namespace App\Repositories;

use App\Core\Repository;

class ShipmentRepository extends Repository
{
    protected string $table = 'shipments';

    public function findBySplitId(int $splitId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE order_seller_split_id=?", [$splitId]);
    }

    public function findWithTracking(int $id): ?array
    {
        $s = $this->db->fetchOne(
            "SELECT sh.*, o.order_number, o.shipping_name, o.shipping_address, o.shipping_city, o.shipping_state, o.shipping_pincode
             FROM `{$this->t()}` sh JOIN `{$this->t('orders')}` o ON o.id=sh.order_id WHERE sh.id=?", [$id]
        );
        if (!$s) return null;
        $s['tracking'] = $this->db->fetchAll(
            "SELECT * FROM `{$this->t('shipment_tracking')}` WHERE shipment_id=? ORDER BY created_at ASC", [$id]
        );
        return $s;
    }

    public function findByOrderId(int $orderId): array
    {
        return $this->db->fetchAll(
            "SELECT sh.*, u.name as seller_name, vp.shop_name FROM `{$this->t()}` sh
             JOIN `{$this->t('users')}` u ON u.id=sh.seller_id
             LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=sh.seller_id
             WHERE sh.order_id=?", [$orderId]
        );
    }

    public function createWithTracking(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->insert($data);
            $this->db->insert(
                "INSERT INTO `{$this->t('shipment_tracking')}` (shipment_id,status,note) VALUES (?,?,?)",
                [$id, $data['status'] ?? 'pending', 'Shipment created']
            );
            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addTrackingUpdate(int $shipmentId, string $status, ?string $location, ?string $note): void
    {
        $this->db->beginTransaction();
        try {
            $timestamps = '';
            $params = [$status];
            if ($status === 'in_transit' || $status === 'picked_up') {
                $timestamps = ", shipped_at=IF(shipped_at IS NULL, NOW(), shipped_at)";
            }
            if ($status === 'delivered') {
                $timestamps = ", delivered_at=IF(delivered_at IS NULL, NOW(), delivered_at)";
            }
            $this->db->execute("UPDATE `{$this->t()}` SET status=?{$timestamps} WHERE id=?", [...$params, $shipmentId]);
            $this->db->insert(
                "INSERT INTO `{$this->t('shipment_tracking')}` (shipment_id,status,location,note) VALUES (?,?,?,?)",
                [$shipmentId, $status, $location, $note]
            );
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getForSeller(int $sellerId, int $page = 1, array $f = []): array
    {
        $where = 'sh.seller_id=?'; $params = [$sellerId];
        if (!empty($f['status'])) { $where .= ' AND sh.status=?'; $params[] = $f['status']; }

        $sql = "SELECT sh.*, o.order_number FROM `{$this->t()}` sh
                JOIN `{$this->t('orders')}` o ON o.id=sh.order_id
                WHERE $where ORDER BY sh.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function getForAdmin(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['status'])) { $where .= ' AND sh.status=?'; $params[] = $f['status']; }
        if (!empty($f['search'])) { $where .= ' AND (sh.tracking_number LIKE ? OR o.order_number LIKE ?)'; $s = '%'.$f['search'].'%'; $params[] = $s; $params[] = $s; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'status', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT sh.*, o.order_number, u.name as seller_name, vp.shop_name
                FROM `{$this->t()}` sh
                JOIN `{$this->t('orders')}` o ON o.id=sh.order_id
                JOIN `{$this->t('users')}` u ON u.id=sh.seller_id
                LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=sh.seller_id
                WHERE $where ORDER BY sh.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }
}
