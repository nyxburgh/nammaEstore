<?php
namespace App\Admin\Services;
use App\Core\Database;

class ActivityService
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function log(string $actorType, int $actorId, string $action, string $module, string $desc = ''): void {
        $tbl   = in_array($actorType, ['admin','sub_admin']) ? DB_PREFIX.'admins' : DB_PREFIX.'users';
        $actor = $this->db->fetchOne("SELECT name FROM `$tbl` WHERE id=?", [$actorId]);
        $this->db->insert(
            "INSERT INTO `".DB_PREFIX."activity_logs` (actor_type,actor_id,actor_name,action,module,description,ip_address,user_agent) VALUES(?,?,?,?,?,?,?,?)",
            [$actorType,$actorId,$actor['name']??'Unknown',$action,$module,$desc,
             $_SERVER['REMOTE_ADDR']??'0.0.0.0', substr($_SERVER['HTTP_USER_AGENT']??'',0,500)]
        );
    }

    public function getLogs(int $page = 1, array $f = []): array {
        $w = '1=1'; $p = [];
        if (!empty($f['module']))     { $w .= " AND module=?";     $p[] = $f['module']; }
        if (!empty($f['actor_type'])) { $w .= " AND actor_type=?"; $p[] = $f['actor_type']; }
        if (!empty($f['search']))     { $w .= " AND (actor_name LIKE ? OR action LIKE ? OR description LIKE ?)"; $s='%'.$f['search'].'%'; $p[]=$s;$p[]=$s;$p[]=$s; }
        if (!empty($f['date']))       { $w .= " AND DATE(created_at)=?"; $p[] = $f['date']; }
        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'actor_name', 'action', 'module', 'created_at'], 'created_at');
        $sortDir = safeSortDir($f['dir'] ?? null);
        return $this->db->paginate("SELECT * FROM `".DB_PREFIX."activity_logs` WHERE $w ORDER BY {$sortCol} {$sortDir}", $p, $page);
    }
}
