<?php
namespace App\SellerPanel\Services;

use App\Repositories\UserRepository;

class SellerSettingsService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function updateSettings(int $sellerId, array $d): void
    {
        $this->users->updateSellerProfile(
            $sellerId,
            [
                'shop_name'   => $d['shop_name']   ?? null,
                'description' => $d['description'] ?? null,
                'gst_number'  => $d['gst_number']   ?? null,
            ],
            [
                'shop_phone' => $d['shop_phone'] ?? null,
                'address'    => $d['address']    ?? null,
                'city'       => $d['city']       ?? null,
                'state'      => $d['state']      ?? null,
                'pincode'    => $d['pincode']     ?? null,
            ]
        );

        if (!empty($d['name'])) {
            $this->users->update($sellerId, [
                'name'  => $d['name'],
                'phone' => $d['phone'] ?? null,
            ]);
            $_SESSION['seller']['name'] = $d['name'];
        }
    }

    public function updatePassword(int $sellerId, string $current, string $new, string $confirm): array
    {
        $u = $this->users->findById($sellerId);

        if (!password_verify($current, $u['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        if (strlen($new) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters.'];
        }
        if ($new !== $confirm) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        $this->users->update($sellerId, [
            'password' => password_hash($new, PASSWORD_DEFAULT),
        ]);
        return ['success' => true];
    }
}
