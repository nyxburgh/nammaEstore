<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\VendorPanel\Services\VendorReturnService;

class ReturnController extends VendorController
{
    public function index(): void
    {
        Middleware::vendorAuth();
        $f = ['status' => $this->input('status', '')];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'       => 'Returns',
            'section'     => 'returns',
            'returnsData' => (new VendorReturnService())->list(Auth::vendorId(), (int) $this->input('page', 1), $f),
            'filters'     => $f,
        ]));
    }
}
