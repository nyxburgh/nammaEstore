<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\SellerPanel\Services\SellerSettingsService;

class SettingsController extends SellerController
{
    private SellerSettingsService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new SellerSettingsService();
    }

    public function index(): void
    {
        Middleware::sellerAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Settings',
            'section' => 'settings',
        ]));
    }

    public function update(): void
    {
        csrf_check();
        Middleware::sellerAuth();
        $this->service->updateSettings(Auth::sellerId(), $_POST);
        $this->setFlash('success', 'Settings saved successfully.');
        $this->redirect(SELLER_URL . '/settings');
    }

    public function updatePassword(): void
    {
        csrf_check();
        Middleware::sellerAuth();
        $r = $this->service->updatePassword(
            Auth::sellerId(),
            $this->input('current_password', ''),
            $this->input('new_password', ''),
            $this->input('confirm_password', '')
        );
        $this->setFlash($r['success'] ? 'success' : 'error', $r['success'] ? 'Password updated successfully.' : $r['message']);
        $this->redirect(SELLER_URL . '/settings');
    }
}
