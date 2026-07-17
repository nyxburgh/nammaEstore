<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\VendorPanel\Services\WalletService;

class WalletController extends VendorController
{
    private function svc(): WalletService { return new WalletService(); }

    public function index(): void
    {
        Middleware::vendorAuth();
        $vid = Auth::vendorId();
        $svc = $this->svc();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'        => 'Wallet & Payouts',
            'section'      => 'wallet',
            'walletData'   => $svc->overview($vid),
            'transactions' => $svc->transactions($vid, (int) $this->input('page', 1)),
            'withdrawalHistory' => $svc->withdrawalHistory($vid, 1),
        ]));
    }

    public function requestWithdrawal(): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $r = $this->svc()->requestWithdrawal(
            Auth::vendorId(),
            (float) $this->input('amount', 0),
            $this->input('method', ''),
            $this->input('method_details', '')
        );
        $this->setFlash($r['success'] ? 'success' : 'error', $r['message']);
        $this->redirect(VENDOR_URL . '/wallet');
    }
}
