<?php
namespace App\Admin\Services;
use App\Core\Database;
use App\Repositories\AdminRepository;

class SubAdminService
{
    private AdminRepository $model;
    private Database   $db;
    public function __construct() { $this->model=new AdminRepository(); $this->db=Database::getInstance(); }

    public function create(array $d, int $byAdmin): array {
        if ($this->model->findByEmail($d['email'])) return ['success'=>false,'message'=>'Email already in use.'];
        $id=$this->model->insert(['name'=>$d['name'],'email'=>$d['email'],'password'=>password_hash($d['password'],PASSWORD_DEFAULT),'role'=>'sub_admin','is_active'=>1,'created_by'=>$byAdmin]);
        if (!empty($d['permissions'])) $this->model->savePermissions($id,$d['permissions']);
        (new ActivityService())->log('admin',$byAdmin,'create_sub_admin','admin_roles',"Created: {$d['name']}");
        return ['success'=>true,'admin_id'=>$id];
    }
    public function update(int $id, array $d, int $byAdmin): array {
        $upd=['name'=>$d['name']];
        if (!empty($d['password'])) $upd['password']=password_hash($d['password'],PASSWORD_DEFAULT);
        $this->model->update($id,$upd);
        if (isset($d['permissions'])) $this->model->savePermissions($id,$d['permissions']);
        (new ActivityService())->log('admin',$byAdmin,'update_sub_admin','admin_roles',"Updated #$id");
        return ['success'=>true];
    }
    public function toggleStatus(int $id, int $byAdmin): void {
        $this->db->execute("UPDATE `".DB_PREFIX."admins` SET is_active=!is_active WHERE id=? AND role='sub_admin'",[$id]);
        (new ActivityService())->log('admin',$byAdmin,'toggle_sub_admin','admin_roles',"Toggled #$id");
    }
    public function delete(int $id, int $byAdmin): array {
        $a=$this->model->findById($id);
        if (!$a||$a['role']!=='sub_admin') return ['success'=>false,'message'=>'Not found.'];
        $this->model->delete($id);
        (new ActivityService())->log('admin',$byAdmin,'delete_sub_admin','admin_roles',"Deleted: {$a['name']}");
        return ['success'=>true];
    }
    public function getAllowedModules(): array { return ['products','payments','reports','activity','banners','settlements','returns','disputes','coupons','gift_cards','brands']; }

    public function list(int $page, array $f = []): array { return $this->model->getSubAdmins($page, $f); }

    public function find(int $id): ?array {
        $sa = $this->model->findById($id);
        return ($sa && $sa['role'] === 'sub_admin') ? $sa : null;
    }

    public function permissionsMap(int $id): array {
        $pm = [];
        foreach ($this->model->getPermissions($id) as $p) $pm[$p['module']] = $p;
        return $pm;
    }
}
