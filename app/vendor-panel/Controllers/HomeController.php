<?php
namespace App\VendorPanel\Controllers;
use App\Core\Middleware;
class HomeController extends VendorController
{
    public function index(): void {
        Middleware::vendorGuest();
        $this->view('auth.login', ['title'=>'Vendor Login'], 'auth');
    }
    public function login(): void {
        Middleware::vendorGuest();
        $this->view('auth.login', ['title'=>'Vendor Login'], 'auth');
    }
}
