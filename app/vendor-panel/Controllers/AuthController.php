<?php
namespace App\VendorPanel\Controllers;
use App\Core\Middleware;
use App\VendorPanel\Services\VendorAuthService;

class AuthController extends VendorController
{
    public function loginForm(): void {
        Middleware::vendorGuest();
        if ($this->input('rejected')) {
            $this->setFlash('error', 'Your vendor application was not approved. Contact support for details.');
        }
        $this->view('auth.login', ['title'=>'Vendor Login'], 'auth');
    }
    public function login(): void {
        csrf_check();
        Middleware::vendorGuest();
        $e = $this->validate(['email'=>'required|email','password'=>'required']);
        if ($e) { $this->setFlash('error', reset($e)); $this->redirect(VENDOR_URL.'/login'); return; }
        $r = (new VendorAuthService())->login($this->input('email'), $this->input('password'));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(VENDOR_URL.'/login'); return; }
        $this->redirect(VENDOR_URL.'/dashboard');
    }
    public function registerForm(): void {
        Middleware::vendorGuest();
        $this->view('auth.register', ['title'=>'Vendor Registration'], 'auth');
    }
    public function register(): void {
        csrf_check();
        Middleware::vendorGuest();
        $e = $this->validate(['name'=>'required','shop_name'=>'required','email'=>'required|email','password'=>'required|min:8']);
        if ($e) { $this->setFlash('error', reset($e)); $this->redirect(VENDOR_URL.'/register'); return; }
        if ($this->input('password') !== $this->input('confirm_password')) {
            $this->setFlash('error','Passwords do not match.'); $this->redirect(VENDOR_URL.'/register'); return;
        }
        $r = (new VendorAuthService())->register($_POST);
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(VENDOR_URL.'/register'); return; }
        $this->redirect(VENDOR_URL.'/verify-email');
    }
    public function logout(): void {
        (new VendorAuthService())->logout();
        $this->redirect(VENDOR_URL.'/login');
    }

    public function verifyEmailForm(): void {
        Middleware::vendorAuth();
        $vendor = \App\Core\Auth::vendor();
        $this->view('auth.verify-email', ['title' => 'Verify Your Email', 'email' => $vendor['email'] ?? ''], 'auth');
    }

    public function verifyEmail(): void {
        csrf_check();
        Middleware::vendorAuth();
        $vendor = \App\Core\Auth::vendor();
        $r = (new VendorAuthService())->verifyEmail((int) $vendor['id'], $vendor['email'], $this->input('otp', ''));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(VENDOR_URL.'/verify-email'); return; }
        $this->setFlash('success', 'Email verified! Your shop is now live.');
        $this->redirect(VENDOR_URL.'/dashboard');
    }

    public function resendOtp(): void {
        csrf_check();
        Middleware::vendorAuth();
        $vendor = \App\Core\Auth::vendor();
        $r = (new VendorAuthService())->resendVerification((int) $vendor['id'], $vendor['email'], $vendor['name'] ?? 'your shop');
        $this->setFlash($r['success'] ? 'success' : 'error', $r['success'] ? 'A new code has been sent to your email.' : $r['message']);
        $this->redirect(VENDOR_URL.'/verify-email');
    }
}
