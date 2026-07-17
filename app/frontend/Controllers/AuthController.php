<?php
namespace App\Frontend\Controllers;
use App\Core\Middleware;
use App\Frontend\Services\{AuthService, CartService, SettingsService};

class AuthController extends FrontendController
{
    /** Shared page data every auth-layout view needs. */
    private function authData(string $title): array {
        return [
            'title'      => $title,
            'cartCount'  => (new CartService())->getCount(),
            'settings'   => SettingsService::all(),
            'categories' => (new \App\Frontend\Services\ProductService())->getCategories(),
        ];
    }

    public function loginForm(): void {
        Middleware::userGuest();
        $showCaptcha = !empty($_SESSION['show_login_captcha']);
        $this->view('auth.login', $this->authData('Login') + ['showCaptcha' => $showCaptcha], 'auth');
    }
    public function login(): void {
        csrf_check();
        Middleware::userGuest();
        $e = $this->validate(['email'=>'required|email','password'=>'required']);
        if ($e) { $this->setFlash('error', reset($e)); $this->redirect(APP_URL.'/login'); return; }
        $r = (new AuthService())->login(
            $this->input('email'), $this->input('password'),
            (bool) $this->input('remember'), $this->input('captcha_answer')
        );
        if (!$r['success']) {
            if (!empty($r['needs_verification'])) {
                $_SESSION['pending_verify_email'] = $r['email'];
                $this->setFlash('info', $r['message']);
                $this->redirect(APP_URL.'/verify-email');
                return;
            }
            $this->setFlash('error', $r['message']);
            if (!empty($r['require_captcha'])) $_SESSION['show_login_captcha'] = true;
            $this->redirect(APP_URL.'/login');
            return;
        }
        unset($_SESSION['show_login_captcha']);
        $this->redirect(APP_URL.'/account');
    }
    public function registerForm(): void {
        Middleware::userGuest();
        $this->view('auth.register', $this->authData('Register'), 'auth');
    }
    public function register(): void {
        csrf_check();
        Middleware::userGuest();
        $e = $this->validate(['name'=>'required','email'=>'required|email','password'=>'required|min:8']);
        if ($e) { $this->setFlash('error', reset($e)); $this->redirect(APP_URL.'/register'); return; }
        if ($this->input('password') !== $this->input('confirm_password')) { $this->setFlash('error','Passwords do not match.'); $this->redirect(APP_URL.'/register'); return; }
        $r = (new AuthService())->register($_POST, $this->input('captcha_answer'));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(APP_URL.'/register'); return; }
        $_SESSION['pending_verify_email'] = $r['email'];
        $this->setFlash('success', 'Account created! Enter the code we emailed you to verify your account.');
        $this->redirect(APP_URL.'/verify-email');
    }
    public function logout(): void {
        (new AuthService())->logout();
        $this->redirect(APP_URL.'/login');
    }

    // ── Signup email verification (OTP) ───────────────────────
    public function verifyForm(): void {
        Middleware::userGuest();
        $email = $_SESSION['pending_verify_email'] ?? '';
        if (!$email) { $this->redirect(APP_URL.'/register'); return; }
        $this->view('auth.verify-email', $this->authData('Verify Your Email') + ['email' => $email], 'auth');
    }
    public function verify(): void {
        csrf_check();
        Middleware::userGuest();
        $email = $_SESSION['pending_verify_email'] ?? '';
        if (!$email) { $this->redirect(APP_URL.'/register'); return; }
        $r = (new AuthService())->verifyEmail($email, $this->input('otp', ''));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(APP_URL.'/verify-email'); return; }
        unset($_SESSION['pending_verify_email']);
        $this->setFlash('success', 'Email verified — welcome to Namma E Store!');
        $this->redirect(APP_URL.'/account');
    }
    public function resendOtp(): void {
        csrf_check();
        Middleware::userGuest();
        $email = $_SESSION['pending_verify_email'] ?? '';
        if (!$email) { $this->redirect(APP_URL.'/register'); return; }
        $r = (new AuthService())->sendVerificationOtp($email);
        $this->setFlash($r['success'] ? 'success' : 'error', $r['success'] ? 'A new code has been sent to your email.' : $r['message']);
        $this->redirect(APP_URL.'/verify-email');
    }

    // ── Forgot / reset password (OTP) ──────────────────────────
    public function forgotForm(): void {
        $this->view('auth.forgot', $this->authData('Forgot Password'), 'auth');
    }
    public function forgot(): void {
        csrf_check();
        $email = trim($this->input('email', ''));
        if ($email === '') { $this->setFlash('error', 'Please enter your email address.'); $this->redirect(APP_URL.'/forgot-password'); return; }
        (new AuthService())->sendPasswordResetOtp($email);
        // Same message whether or not the address is registered — the
        // form must not reveal which emails have accounts.
        $_SESSION['pending_reset_email'] = $email;
        $this->setFlash('info', 'If this email is registered, a 6-digit reset code has been sent to it.');
        $this->redirect(APP_URL.'/reset-password');
    }
    public function resetForm(): void {
        $email = $_SESSION['pending_reset_email'] ?? '';
        if (!$email) { $this->redirect(APP_URL.'/forgot-password'); return; }
        $this->view('auth.reset', $this->authData('Reset Password') + ['email' => $email], 'auth');
    }
    public function reset(): void {
        csrf_check();
        $email = $_SESSION['pending_reset_email'] ?? '';
        if (!$email) { $this->redirect(APP_URL.'/forgot-password'); return; }
        if ($this->input('new_password') !== $this->input('confirm_password')) {
            $this->setFlash('error', 'Passwords do not match.'); $this->redirect(APP_URL.'/reset-password'); return;
        }
        $r = (new AuthService())->resetPassword($email, $this->input('otp', ''), $this->input('new_password', ''));
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->redirect(APP_URL.'/reset-password'); return; }
        unset($_SESSION['pending_reset_email']);
        $this->setFlash('success', 'Password reset successfully. Sign in with your new password.');
        $this->redirect(APP_URL.'/login');
    }
}
