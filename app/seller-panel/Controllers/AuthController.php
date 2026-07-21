<?php
namespace App\SellerPanel\Controllers;
use App\Core\Middleware;
use App\SellerPanel\Services\SellerAuthService;

class AuthController extends SellerController
{
    public function loginForm(): void {
        Middleware::sellerGuest();
        if ($this->input('rejected')) {
            $this->setFlash('error', 'Your seller application was not approved. Contact support for details.');
        }
        $this->view('auth.login', ['title'=>'Seller Login'], 'auth');
    }
    public function login(): void {
        csrf_check();
        Middleware::sellerGuest();
        $e = $this->validate(['email'=>'required|email','password'=>'required']);
        if ($e) { $this->setFlash('error', reset($e)); $this->redirect(SELLER_URL.'/login'); return; }
        $r = (new SellerAuthService())->login($this->input('email'), $this->input('password'));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(SELLER_URL.'/login'); return; }
        $this->redirect(SELLER_URL.'/dashboard');
    }
    public function registerForm(): void {
        Middleware::sellerGuest();
        $this->view('auth.register', ['title'=>'Seller Registration'], 'auth');
    }
    public function register(): void {
        csrf_check();
        Middleware::sellerGuest();
        $e = $this->validate(['name'=>'required','shop_name'=>'required','email'=>'required|email','password'=>'required|min:8']);
        if ($e) { $this->setFlash('error', reset($e)); $this->redirect(SELLER_URL.'/register'); return; }
        if ($this->input('password') !== $this->input('confirm_password')) {
            $this->setFlash('error','Passwords do not match.'); $this->redirect(SELLER_URL.'/register'); return;
        }
        $r = (new SellerAuthService())->register($_POST);
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(SELLER_URL.'/register'); return; }
        $this->redirect(SELLER_URL.'/verify-email');
    }
    public function logout(): void {
        (new SellerAuthService())->logout();
        $this->redirect(SELLER_URL.'/login');
    }

    public function verifyEmailForm(): void {
        Middleware::sellerAuth();
        $seller = \App\Core\Auth::seller();
        $this->view('auth.verify-email', ['title' => 'Verify Your Email', 'email' => $seller['email'] ?? ''], 'auth');
    }

    public function verifyEmail(): void {
        csrf_check();
        Middleware::sellerAuth();
        $seller = \App\Core\Auth::seller();
        $r = (new SellerAuthService())->verifyEmail((int) $seller['id'], $seller['email'], $this->input('otp', ''));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(SELLER_URL.'/verify-email'); return; }
        $this->setFlash('success', 'Email verified! Your shop is now live.');
        $this->redirect(SELLER_URL.'/dashboard');
    }

    public function resendOtp(): void {
        csrf_check();
        Middleware::sellerAuth();
        $seller = \App\Core\Auth::seller();
        $r = (new SellerAuthService())->resendVerification((int) $seller['id'], $seller['email'], $seller['name'] ?? 'your shop');
        $this->setFlash($r['success'] ? 'success' : 'error', $r['success'] ? 'A new code has been sent to your email.' : $r['message']);
        $this->redirect(SELLER_URL.'/verify-email');
    }
}
