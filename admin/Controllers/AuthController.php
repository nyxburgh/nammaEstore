<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\AuthService;

class AuthController extends AdminController
{
    public function index(): void { $this->redirect(ADMIN_URL . '/login'); }

    public function loginForm(): void {
        Middleware::adminGuest();
        $this->view('auth.login', ['title' => 'Admin Login'], 'auth');
    }

    public function login(): void {
        csrf_check();
        Middleware::adminGuest();
        $errors = $this->validate(['email' => 'required|email', 'password' => 'required|min:6']);
        if ($errors) { $this->setFlash('error', reset($errors)); $this->redirect(ADMIN_URL . '/login'); return; }
        $result = (new AuthService())->attempt($this->input('email'), $this->input('password'));
        if (!$result['success']) { $this->setFlash('error', $result['message']); $this->redirect(ADMIN_URL . '/login'); return; }
        $this->redirect(ADMIN_URL . '/dashboard');
    }

    public function logout(): void {
        csrf_check();
        Middleware::adminAuth();
        $a = Auth::admin();
        (new AuthService())->logout($a['id'], $a['name']);
        $this->redirect(ADMIN_URL . '/login');
    }
}
