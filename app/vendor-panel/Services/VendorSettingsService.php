<?php
namespace App\VendorPanel\Services;

use App\Repositories\UserRepository;

class VendorSettingsService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function updateSettings(int $vendorId, array $d): void
    {
        $this->users->updateVendorProfile(
            $vendorId,
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
            $this->users->update($vendorId, [
                'name'  => $d['name'],
                'phone' => $d['phone'] ?? null,
            ]);
            $_SESSION['vendor']['name'] = $d['name'];
        }
    }

    public function updatePassword(int $vendorId, string $current, string $new, string $confirm): array
    {
        $u = $this->users->findById($vendorId);

        if (!password_verify($current, $u['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        if (strlen($new) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters.'];
        }
        if ($new !== $confirm) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        $this->users->update($vendorId, [
            'password' => password_hash($new, PASSWORD_DEFAULT),
        ]);
        return ['success' => true];
    }
}
