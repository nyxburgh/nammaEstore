<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\SystemSettingsService;

class SettingsController extends AdminController
{
    private SystemSettingsService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new SystemSettingsService();
    }

    public function index(): void {
        Middleware::superAdmin();
        $this->view('settings.index',['title'=>'System Settings','grouped'=>$this->service->grouped()]);
    }
    public function update(): void {
        csrf_check();
        Middleware::superAdmin();
        $this->service->updateMany($_POST['settings'] ?? []);
        $this->setFlash('success','Settings saved successfully.'); $this->redirect(ADMIN_URL.'/settings');
    }
}
