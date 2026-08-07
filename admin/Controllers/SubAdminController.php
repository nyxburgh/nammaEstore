<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\SubAdminService;

class SubAdminController extends AdminController
{
    private SubAdminService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new SubAdminService();
    }

    public function index(): void {
        Middleware::superAdmin();
        $f = ['search' => $this->input('search',''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('sub-admins.index',['title'=>'Sub-Admins','subAdmins'=>$this->service->list((int)$this->input('page',1), $f),'filters'=>$f]);
    }
    public function create(): void {
        csrf_check();
        Middleware::superAdmin();
        $this->view('sub-admins.create',['title'=>'Add Sub-Admin','modules'=>$this->service->getAllowedModules()]);
    }
    public function store(): void {
        csrf_check();
        Middleware::superAdmin();
        $e = $this->validate(['name'=>'required','email'=>'required|email','password'=>'required|min:8']);
        if ($e) { $this->setFlash('error',reset($e)); $this->backWithInput(); return; }
        $d = $this->inputs();
        $d['permissions'] = $this->buildPerms($_POST['modules']??[]);
        $r = $this->service->create($d,Auth::adminId());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->backWithInput(); return; }
        $this->setFlash('success','Sub-admin created.'); $this->redirect(ADMIN_URL.'/sub-admins');
    }
    public function edit(string $id): void {
        Middleware::superAdmin();
        $sa = $this->service->find((int)$id);
        if (!$sa) { $this->redirect(ADMIN_URL.'/sub-admins'); return; }
        $pm = $this->service->permissionsMap((int)$id);
        $this->view('sub-admins.edit',['title'=>'Edit Sub-Admin','subAdmin'=>$sa,'modules'=>$this->service->getAllowedModules(),'permMap'=>$pm]);
    }
    public function update(string $id): void {
        csrf_check();
        Middleware::superAdmin();
        $d = $this->inputs();
        $d['permissions'] = $this->buildPerms($_POST['modules']??[]);
        $r = $this->service->update((int)$id,$d,Auth::adminId());
        $this->setFlash($r['success']?'success':'error',$r['success']?'Sub-admin updated.':($r['message']??''));
        $this->redirect(ADMIN_URL.'/sub-admins');
    }
    public function delete(string $id): void {
        csrf_check();
        Middleware::superAdmin();
        $r = $this->service->delete((int)$id,Auth::adminId());
        $this->json($r['success'] ? ['success'=>true] : ['success'=>false,'message'=>$r['message']??'Delete failed.']);
    }
    public function toggleStatus(string $id): void { Middleware::superAdmin(); $this->service->toggleStatus((int)$id,Auth::adminId()); $this->json(['success'=>true]); }

    private function buildPerms(array $modules): array {
        $out = [];
        foreach ($modules as $m) {
            $out[$m] = ['view'=>1,'create'=>(int)isset($_POST['perm'][$m]['create']),'edit'=>(int)isset($_POST['perm'][$m]['edit']),'delete'=>(int)isset($_POST['perm'][$m]['delete'])];
        }
        return $out;
    }
}
