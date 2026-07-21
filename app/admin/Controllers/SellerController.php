<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\SellerService;

class SellerController extends AdminController
{
    private SellerService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new SellerService();
    }

    public function index(): void {
        Middleware::adminAuth();
        $f = ['search'=>$this->input('search'),'status'=>$this->input('status'),'is_active'=>$this->input('is_active'),'sort'=>$this->input('sort'),'dir'=>$this->input('dir')];
        $this->view('sellers.index',['title'=>'Sellers','sellers'=>$this->service->list((int)$this->input('page',1),$f),'filters'=>$f]);
    }
    public function create(): void { Middleware::superAdmin(); $this->view('sellers.create',['title'=>'Add Seller']); }
    public function store(): void {
        csrf_check();
        Middleware::superAdmin();
        $e = $this->validate(['name'=>'required','email'=>'required|email','password'=>'required|min:8','shop_name'=>'required']);
        if ($e) { $this->setFlash('error',reset($e)); $this->back(); return; }
        $r = $this->service->create($this->inputs(), Auth::adminId());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->back(); return; }
        $this->setFlash('success','Seller created successfully.');
        $this->redirect(ADMIN_URL.'/sellers');
    }
    public function show(string $id): void {
        Middleware::adminAuth();
        $v = $this->service->find((int)$id);
        if (!$v) { $this->redirect(ADMIN_URL.'/sellers'); return; }
        $this->view('sellers.show',['title'=>e($v['shop_name']??$v['name']),'seller'=>$v,'earnings'=>$this->service->getEarnings((int)$id)]);
    }
    public function edit(string $id): void {
        Middleware::superAdmin();
        $v = $this->service->find((int)$id);
        if (!$v) { $this->redirect(ADMIN_URL.'/sellers'); return; }
        $this->view('sellers.edit', ['title'=>'Edit Seller: '.e($v['shop_name']??$v['name']),'seller'=>$v]);
    }
    public function update(string $id): void {
        csrf_check();
        Middleware::superAdmin();
        $r = $this->service->update((int)$id, $this->inputs(), Auth::adminId());
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(ADMIN_URL.'/sellers/'.$id.'/edit'); return; }
        $this->setFlash('success', 'Seller updated successfully.');
        $this->redirect(ADMIN_URL.'/sellers/'.$id);
    }
    public function approve(string $id): void  { Middleware::superAdmin(); $r=$this->service->approve((int)$id,Auth::adminId()); $this->setFlash($r['success']?'success':'error',$r['message']); $this->back(); }
    public function reject(string $id): void   { Middleware::superAdmin(); $r=$this->service->reject((int)$id,Auth::adminId(),$this->input('rejection_note','')); $this->setFlash($r['success']?'success':'error',$r['message']); $this->back(); }
    public function suspend(string $id): void  { Middleware::superAdmin(); $r=$this->service->suspend((int)$id,Auth::adminId()); $this->setFlash($r['success']?'success':'error',$r['message']); $this->back(); }
    public function toggleStatus(string $id): void { Middleware::superAdmin(); $this->service->toggleStatus((int)$id); $this->json(['success'=>true]); }
}
