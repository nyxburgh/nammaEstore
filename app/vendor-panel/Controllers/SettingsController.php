<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\VendorPanel\Services\VendorSettingsService;

class SettingsController extends VendorController
{
    private VendorSettingsService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new VendorSettingsService();
    }

    public function index(): void
    {
        Middleware::vendorAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Settings',
            'section' => 'settings',
        ]));
    }

    public function update(): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $this->service->updateSettings(Auth::vendorId(), $_POST);
        $this->setFlash('success', 'Settings saved successfully.');
        $this->redirect(VENDOR_URL . '/settings');
    }

    public function updatePassword(): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $r = $this->service->updatePassword(
            Auth::vendorId(),
            $this->input('current_password', ''),
            $this->input('new_password', ''),
            $this->input('confirm_password', '')
        );
        $this->setFlash($r['success'] ? 'success' : 'error', $r['success'] ? 'Password updated successfully.' : $r['message']);
        $this->redirect(VENDOR_URL . '/settings');
    }
}
