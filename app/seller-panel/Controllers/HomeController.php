<?php
namespace App\SellerPanel\Controllers;
use App\Core\Middleware;
class HomeController extends SellerController
{
    public function index(): void {
        Middleware::sellerGuest();
        $this->view('auth.login', ['title'=>'Seller Login'], 'auth');
    }
    public function login(): void {
        Middleware::sellerGuest();
        $this->view('auth.login', ['title'=>'Seller Login'], 'auth');
    }
}
