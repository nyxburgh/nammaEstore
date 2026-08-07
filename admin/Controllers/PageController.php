<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\PageService;

class PageController extends AdminController
{
    private PageService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new PageService();
    }

    public function index(): void {
        Middleware::can('pages');
        $f = ['search'=>$this->input('search'), 'sort'=>$this->input('sort'), 'dir'=>$this->input('dir')];
        $this->view('pages.index',['title'=>'Info Pages','pages'=>$this->service->list((int)$this->input('page',1),$f),'filters'=>$f]);
    }
    public function create(): void { Middleware::can('pages','create'); $this->view('pages.create',['title'=>'Add Page']); }
    public function store(): void {
        csrf_check();
        Middleware::can('pages','create');
        $e = $this->validate(['title'=>'required']);
        if ($e) { $this->setFlash('error',reset($e)); $this->backWithInput(); return; }
        $r = $this->service->create($this->inputs(), Auth::adminId());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->backWithInput(); return; }
        $this->setFlash('success','Page created.'); $this->redirect(ADMIN_URL.'/pages');
    }
    public function edit(string $id): void {
        Middleware::can('pages','edit');
        $pg = $this->service->find((int)$id);
        if (!$pg) { $this->redirect(ADMIN_URL.'/pages'); return; }
        $this->view('pages.edit',['title'=>'Edit Page','pageRow'=>$pg]);
    }
    public function update(string $id): void {
        csrf_check();
        Middleware::can('pages','edit');
        $r = $this->service->update((int)$id, $this->inputs(), Auth::adminId());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->backWithInput(); return; }
        $this->setFlash('success','Page updated.'); $this->redirect(ADMIN_URL.'/pages');
    }
    public function delete(string $id): void { Middleware::can('pages','delete'); csrf_check(); $this->service->delete((int)$id); $this->json(['success'=>true]); }
    public function toggleStatus(string $id): void { Middleware::can('pages','edit'); $this->service->toggleStatus((int)$id); $this->json(['success'=>true]); }
}
