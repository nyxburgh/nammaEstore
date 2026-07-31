<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\SellerPanel\Services\WalletService;

class WalletController extends SellerController
{
    private function svc(): WalletService { return new WalletService(); }

    public function index(): void
    {
        Middleware::sellerAuth();
        $vid = Auth::sellerId();
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
        Middleware::sellerAuth();
        $r = $this->svc()->requestWithdrawal(
            Auth::sellerId(),
            (float) $this->input('amount', 0),
            $this->input('method', ''),
            $this->input('method_details', '')
        );
        $this->setFlash($r['success'] ? 'success' : 'error', $r['message']);
        $this->redirect(SELLER_URL . '/wallet');
    }
}
