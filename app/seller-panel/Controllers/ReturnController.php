<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\SellerPanel\Services\SellerReturnService;

class ReturnController extends SellerController
{
    public function index(): void
    {
        Middleware::sellerAuth();
        $f = ['status' => $this->input('status', '')];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'       => 'Returns',
            'section'     => 'returns',
            'returnsData' => (new SellerReturnService())->list(Auth::sellerId(), (int) $this->input('page', 1), $f),
            'filters'     => $f,
        ]));
    }
}
