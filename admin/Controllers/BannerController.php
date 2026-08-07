<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\BannerService;

class BannerController extends AdminController
{
    private BannerService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new BannerService();
    }

    public function index(): void {
        Middleware::can('banners');
        $f = ['position'=>$this->input('position'), 'search'=>$this->input('search'), 'sort'=>$this->input('sort'), 'dir'=>$this->input('dir')];
        $this->view('banners.index',['title'=>'Banners','banners'=>$this->service->list((int)$this->input('page',1),$f),'filters'=>$f]);
    }
    public function create(): void { Middleware::can('banners','create'); $this->view('banners.create',['title'=>'Add Banner']); }
    public function store(): void {
        csrf_check();
        Middleware::can('banners','create');
        $e = $this->validate(['title'=>'required','position'=>'required']);
        if ($e) { $this->setFlash('error',reset($e)); $this->backWithInput(); return; }
        $r = $this->service->create($this->inputs(), $_FILES, Auth::adminId());
        if (!$r['success']) { $this->setFlash('error',$r['message']); $this->backWithInput(); return; }
        $this->setFlash('success','Banner created.'); $this->redirect(ADMIN_URL.'/banners');
    }
    public function edit(string $id): void {
        Middleware::can('banners','edit');
        $b = $this->service->find((int)$id);
        if (!$b) { $this->redirect(ADMIN_URL.'/banners'); return; }
        $this->view('banners.edit',['title'=>'Edit Banner','banner'=>$b]);
    }
    public function update(string $id): void {
        csrf_check();
        Middleware::can('banners','edit');
        $this->service->update((int)$id, $this->inputs(), $_FILES);
        $this->setFlash('success','Banner updated.'); $this->redirect(ADMIN_URL.'/banners');
    }
    public function delete(string $id): void { Middleware::can('banners','delete'); csrf_check(); $this->service->delete((int)$id); $this->json(['success'=>true]); }
    public function toggleStatus(string $id): void { Middleware::can('banners','edit'); $this->service->toggleStatus((int)$id); $this->json(['success'=>true]); }
}
