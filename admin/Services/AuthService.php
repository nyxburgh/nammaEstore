<?php
namespace App\Admin\Services;
use App\Core\{Auth, RateLimiter};
use App\Repositories\AdminRepository;

class AuthService
{
    private AdminRepository $model;
    public function __construct() { $this->model = new AdminRepository(); }

    public function attempt(string $email, string $password): array {
        $rlKey = RateLimiter::keyFor('admin_login', $email);
        if (RateLimiter::tooManyAttempts($rlKey, 10, 15)) {
            return ['success' => false, 'message' => 'Too many login attempts. Try again in ' . RateLimiter::availableInMinutes($rlKey) . ' minute(s).'];
        }

        $admin = $this->model->findByEmail($email);

        if ($admin && !empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            $mins = (int) ceil((strtotime($admin['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account temporarily locked. Try again in {$mins} minute(s)."];
        }

        if (!$admin || !password_verify($password, $admin['password'])) {
            RateLimiter::hit($rlKey, 10, 15);
            if ($admin) {
                $attempts = (int) $admin['failed_login_attempts'] + 1;
                $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 15 * 60) : null;
                $this->model->update($admin['id'], ['failed_login_attempts' => $attempts, 'locked_until' => $lockedUntil]);
            }
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if (!$admin['is_active']) return ['success' => false, 'message' => 'Account has been deactivated.'];

        RateLimiter::clear($rlKey);
        Auth::loginAdmin($admin);
        $this->model->update($admin['id'], ['failed_login_attempts' => 0, 'locked_until' => null]);
        $this->model->updateLastLogin($admin['id']);
        if ($admin['role']==='sub_admin') Auth::loadPermissions($this->model->getPermissions($admin['id']));
        (new ActivityService())->log('admin',$admin['id'],'login','auth','Admin logged in');
        return ['success'=>true];
    }

    public function logout(int $id, string $name): void {
        (new ActivityService())->log('admin',$id,'logout','auth','Admin logged out');
        Auth::logoutAdmin();
        session_destroy();
    }
}
