<?php
namespace App\Repositories;

use App\Core\Repository;

class InvoiceRepository extends Repository
{
    protected string $table = 'invoices';

    public function findBySplitId(int $splitId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE order_vendor_split_id=?", [$splitId]);
    }

    public function findWithItems(int $id): ?array
    {
        $inv = $this->db->fetchOne(
            "SELECT i.*, u.name as vendor_name, vp.shop_name, vp.gst_number as vendor_gst, vp.address as vendor_address,
                    vp.city as vendor_city, vp.state as vendor_state, vp.pincode as vendor_pincode,
                    cu.name as customer_name, cu.email as customer_email,
                    o.order_number, o.shipping_name, o.shipping_address, o.shipping_city, o.shipping_state, o.shipping_pincode
             FROM `{$this->t()}` i
             JOIN `{$this->t('users')}` u ON u.id=i.vendor_id
             LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=i.vendor_id
             JOIN `{$this->t('users')}` cu ON cu.id=i.user_id
             JOIN `{$this->t('orders')}` o ON o.id=i.order_id
             WHERE i.id=?", [$id]
        );
        if (!$inv) return null;
        $inv['items'] = $this->db->fetchAll("SELECT * FROM `{$this->t('invoice_items')}` WHERE invoice_id=?", [$id]);
        return $inv;
    }

    public function insertWithItems(array $invoice, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->insert($invoice);
            foreach ($items as $item) {
                $item['invoice_id'] = $id;
                $cols = '`' . implode('`,`', array_keys($item)) . '`';
                $ph   = implode(',', array_fill(0, count($item), '?'));
                $this->db->insert("INSERT INTO `{$this->t('invoice_items')}` ({$cols}) VALUES ({$ph})", array_values($item));
            }
            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function setPdfPath(int $id, string $path): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET pdf_path=? WHERE id=?", [$path, $id]);
    }

    public function markEmailed(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET emailed_at=NOW() WHERE id=?", [$id]);
    }

    public function getForCustomer(int $userId, int $page = 1): array
    {
        $sql = "SELECT i.*, o.order_number, u.name as vendor_name, vp.shop_name
                FROM `{$this->t()}` i
                JOIN `{$this->t('orders')}` o ON o.id=i.order_id
                JOIN `{$this->t('users')}` u ON u.id=i.vendor_id
                LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=i.vendor_id
                WHERE i.user_id=? ORDER BY i.created_at DESC";
        return $this->paginate($sql, [$userId], $page);
    }

    public function getForVendor(int $vendorId, int $page = 1, array $f = []): array
    {
        $where = 'i.vendor_id=?'; $params = [$vendorId];
        if (!empty($f['date_from'])) { $where .= ' AND i.invoice_date>=?'; $params[] = $f['date_from']; }
        if (!empty($f['date_to']))   { $where .= ' AND i.invoice_date<=?'; $params[] = $f['date_to']; }

        $sql = "SELECT i.*, o.order_number FROM `{$this->t()}` i
                JOIN `{$this->t('orders')}` o ON o.id=i.order_id
                WHERE $where ORDER BY i.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function getForAdmin(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['search']))    { $where .= ' AND (i.invoice_number LIKE ? OR o.order_number LIKE ?)'; $s = '%'.$f['search'].'%'; $params[] = $s; $params[] = $s; }
        if (!empty($f['date_from'])) { $where .= ' AND i.invoice_date>=?'; $params[] = $f['date_from']; }
        if (!empty($f['date_to']))   { $where .= ' AND i.invoice_date<=?'; $params[] = $f['date_to']; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'invoice_number', 'invoice_date', 'grand_total'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT i.*, o.order_number, u.name as vendor_name, vp.shop_name
                FROM `{$this->t()}` i
                JOIN `{$this->t('orders')}` o ON o.id=i.order_id
                JOIN `{$this->t('users')}` u ON u.id=i.vendor_id
                LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=i.vendor_id
                WHERE $where ORDER BY i.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    /** GST summary for reporting/export — grouped by rate for a period. */
    public function getGstSummary(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT ii.gst_rate,
                    COUNT(DISTINCT i.id) as invoice_count,
                    SUM(ii.taxable_amount) as taxable_amount,
                    SUM(ii.cgst_amount) as cgst_amount,
                    SUM(ii.sgst_amount) as sgst_amount,
                    SUM(ii.igst_amount) as igst_amount
             FROM `{$this->t('invoice_items')}` ii
             JOIN `{$this->t()}` i ON i.id=ii.invoice_id
             WHERE i.invoice_date BETWEEN ? AND ?
             GROUP BY ii.gst_rate ORDER BY ii.gst_rate",
            [$from, $to]
        );
    }

    public function getForExport(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT i.invoice_number, i.invoice_date, o.order_number, vp.shop_name, vp.gst_number as vendor_gst,
                    i.place_of_supply, i.is_interstate, i.taxable_amount, i.cgst_amount, i.sgst_amount, i.igst_amount, i.grand_total
             FROM `{$this->t()}` i
             JOIN `{$this->t('orders')}` o ON o.id=i.order_id
             LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=i.vendor_id
             WHERE i.invoice_date BETWEEN ? AND ?
             ORDER BY i.invoice_date, i.invoice_number",
            [$from, $to]
        );
    }
}
