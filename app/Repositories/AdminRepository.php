<?php
namespace App\Repositories;

use App\Core\Repository;

class AdminRepository extends Repository
{
    protected string $table = 'admins';

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE email=? AND is_active=1", [$email]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function getSubAdmins(int $page = 1, array $f = []): array
    {
        $where = "a.role='sub_admin'"; $params = [];
        if (!empty($f['search'])) {
            $where .= ' AND (a.name LIKE ? OR a.email LIKE ?)';
            $s = '%' . $f['search'] . '%'; $params[] = $s; $params[] = $s;
        }
        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'name', 'created_at', 'last_login'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT a.*, (SELECT COUNT(*) FROM `{$this->t('admin_permissions')}` WHERE admin_id=a.id) as perm_count
                FROM `{$this->t()}` a WHERE $where ORDER BY a.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function getPermissions(int $adminId): array
    {
        return $this->db->fetchAll("SELECT * FROM `{$this->t('admin_permissions')}` WHERE admin_id=?", [$adminId]);
    }

    public function savePermissions(int $adminId, array $modules): void
    {
        $this->db->execute("DELETE FROM `{$this->t('admin_permissions')}` WHERE admin_id=?", [$adminId]);
        foreach ($modules as $module => $actions) {
            $this->db->insert(
                "INSERT INTO `{$this->t('admin_permissions')}` (admin_id,module,can_view,can_create,can_edit,can_delete) VALUES(?,?,?,?,?,?)",
                [$adminId, $module, (int) ($actions['view'] ?? 1), (int) ($actions['create'] ?? 0), (int) ($actions['edit'] ?? 0), (int) ($actions['delete'] ?? 0)]
            );
        }
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET last_login=NOW() WHERE id=?", [$id]);
    }
}
