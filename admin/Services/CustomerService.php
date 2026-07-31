<?php
namespace App\Admin\Services;

use App\Repositories\UserRepository;

class CustomerService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->users->getCustomers($page, $filters);
    }

    public function find(int $id): ?array
    {
        $u = $this->users->findById($id);
        return ($u && $u['role'] === 'customer') ? $u : null;
    }

    public function create(array $d, int $adminId): array
    {
        if ($this->users->findByEmail($d['email'])) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }
        $id = $this->users->insert([
            'name'       => $d['name'],
            'email'      => $d['email'],
            'phone'      => $d['phone'] ?? null,
            'password'   => password_hash($d['password'], PASSWORD_DEFAULT),
            'role'       => 'customer',
            'is_active'  => 1,
            'is_verified'=> 1,
            'created_by' => $adminId,
        ]);
        return ['success' => true, 'customer_id' => $id];
    }

    public function toggleStatus(int $id): void
    {
        $this->users->toggleStatus($id);
    }

    public function update(int $id, array $d): array
    {
        if (empty(trim($d['name'] ?? ''))) return ['success' => false, 'message' => 'Name is required.'];
        $existing = $this->users->findByEmail($d['email']);
        if ($existing && (int) $existing['id'] !== $id) {
            return ['success' => false, 'message' => 'Email already in use by another account.'];
        }
        $this->users->update($id, [
            'name'      => $d['name'],
            'email'     => $d['email'],
            'phone'     => $d['phone'] ?? null,
            'is_active' => isset($d['is_active']) ? (int) $d['is_active'] : 1,
        ]);
        if (!empty($d['password'])) {
            if (strlen($d['password']) < 8) return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
            $this->users->update($id, ['password' => password_hash($d['password'], PASSWORD_DEFAULT)]);
        }
        return ['success' => true];
    }
}
