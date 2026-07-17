<?php
namespace App\Repositories;

use App\Core\Repository;

class UserRepository extends Repository
{
    protected string $table = 'users';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function getAllUsers(int $page = 1, array $f = []): array
    {
        $w = '1=1'; $p = [];
        if (!empty($f['role']))   { $w .= " AND u.role=?"; $p[] = $f['role']; }
        if (!empty($f['search'])) { $w .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)"; $s = '%' . $f['search'] . '%'; $p[] = $s; $p[] = $s; $p[] = $s; }
        if (isset($f['is_active']) && $f['is_active'] !== '') { $w .= " AND u.is_active=?"; $p[] = (int) $f['is_active']; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'name', 'email', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT u.*, vp.shop_name, vp.status as vendor_status
                FROM `{$this->t()}` u
                LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=u.id
                WHERE $w ORDER BY u.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $p, $page);
    }

    public function getVendors(int $page = 1, array $f = []): array
    {
        $f['role'] = 'vendor';
        return $this->getAllUsers($page, $f);
    }

    public function getCustomers(int $page = 1, array $f = []): array
    {
        $f['role'] = 'customer';
        return $this->getAllUsers($page, $f);
    }

    public function getVendorProfile(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT u.*, vp.*, sp.name as plan_name, sp.price as plan_price,
                    vs.expires_at, vs.started_at,
                    COALESCE(vmt.total_turnover,0) as monthly_turnover
             FROM `{$this->t()}` u
             LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=u.id
             LEFT JOIN `{$this->t('vendor_subscriptions')}` vs ON vs.vendor_id=u.id AND vs.status='active'
             LEFT JOIN `{$this->t('subscription_plans')}` sp ON sp.id=vs.plan_id
             LEFT JOIN `{$this->t('vendor_monthly_turnover')}` vmt ON vmt.vendor_id=u.id AND vmt.month=MONTH(NOW()) AND vmt.year=YEAR(NOW())
             WHERE u.id=? AND u.role='vendor'", [$userId]);
    }

    public function getStats(): array
    {
        return [
            'total_customers' => $this->count("role='customer'"),
            'total_vendors'   => $this->count("role='vendor'"),
            'active_vendors'  => (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM `{$this->t()}` u JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=u.id WHERE u.role='vendor' AND vp.status='active'")['c'] ?? 0),
            'pending_vendors' => (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM `{$this->t('vendor_profiles')}` WHERE status='pending'")['c'] ?? 0),
            'new_this_month'  => $this->count("MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"),
        ];
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE email=?", [$email]);
    }

    public function toggleStatus(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET is_active=!is_active WHERE id=?", [$id]);
    }

    public function updateVendorStatus(int $userId, string $status, ?int $adminId = null, ?string $note = null): void
    {
        $this->db->execute(
            "UPDATE `{$this->t('vendor_profiles')}` SET status=?, approved_by=?, approved_at=IF(?='active',NOW(),NULL), rejection_note=? WHERE user_id=?",
            [$status, $adminId, $status, $note, $userId]
        );
    }

    public function getAllVendors(): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, vp.shop_name FROM `{$this->t()}` u
             LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=u.id
             WHERE u.role='vendor' ORDER BY vp.shop_name, u.name"
        );
    }

    public function updateVendorProfile(int $userId, array $core, array $extended): void
    {
        $this->db->execute(
            "UPDATE `{$this->t('vendor_profiles')}` SET
                shop_name=?, description=?, gst_number=?,
                shop_phone=?, address=?, city=?, state=?, pincode=?
             WHERE user_id=?",
            [
                $core['shop_name'] ?? null, $core['description'] ?? null, $core['gst_number'] ?? null,
                $extended['shop_phone'] ?? null, $extended['address'] ?? null, $extended['city'] ?? null,
                $extended['state'] ?? null, $extended['pincode'] ?? null,
                $userId,
            ]
        );
    }
}
