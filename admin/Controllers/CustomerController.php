<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\CustomerService;

class CustomerController extends AdminController
{
    private CustomerService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new CustomerService();
    }

    public function index(): void {
        Middleware::adminAuth();
        $f = ['search'=>$this->input('search'),'is_active'=>$this->input('is_active'),'sort'=>$this->input('sort'),'dir'=>$this->input('dir')];
        $this->view('customers.index',['title'=>'Customers','customers'=>$this->service->list((int)$this->input('page',1),$f),'filters'=>$f]);
    }
    public function show(string $id): void {
        Middleware::adminAuth();
        $c = $this->service->find((int)$id);
        if (!$c) { $this->redirect(ADMIN_URL.'/customers'); return; }
        $this->view('customers.show',['title'=>e($c['name']),'customer'=>$c]);
    }
    public function create(): void { Middleware::superAdmin(); $this->view('customers.create',['title'=>'Add Customer']); }
    public function store(): void {
        csrf_check();
        Middleware::superAdmin();
        $e = $this->validate(['name'=>'required','email'=>'required|email','password'=>'required|min:8']);
        if ($e) { $this->setFlash('error',reset($e)); $this->backWithInput(); return; }
        $r = $this->service->create($this->inputs(), Auth::adminId());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->backWithInput(); return; }
        $this->setFlash('success','Customer created.'); $this->redirect(ADMIN_URL.'/customers');
    }
    public function edit(string $id): void {
        Middleware::superAdmin();
        $c = $this->service->find((int)$id);
        if (!$c) { $this->redirect(ADMIN_URL.'/customers'); return; }
        $this->view('customers.edit', ['title' => 'Edit: ' . e($c['name']), 'customer' => $c]);
    }
    public function update(string $id): void {
        csrf_check();
        Middleware::superAdmin();
        $r = $this->service->update((int)$id, $this->inputs());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->backWithInput(); return; }
        $this->setFlash('success','Customer updated.'); $this->redirect(ADMIN_URL.'/customers/'.$id);
    }
    public function toggleStatus(string $id): void { Middleware::superAdmin(); $this->service->toggleStatus((int)$id); $this->json(['success'=>true]); }
}
