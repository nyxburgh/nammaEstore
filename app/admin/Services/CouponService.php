<?php
namespace App\Admin\Services;

use App\Repositories\CouponRepository;

class CouponService
{
    private CouponRepository $coupons;

    public function __construct()
    {
        $this->coupons = new CouponRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->coupons->getAll($page, $filters);
    }

    public function find(int $id): ?array
    {
        return $this->coupons->findById($id);
    }

    public function create(array $d, int $adminId): array
    {
        $code = strtoupper(trim($d['code'] ?? ''));
        if (!$code) {
            return ['success' => false, 'message' => 'Coupon code is required.'];
        }
        if ($this->coupons->findByCode($code)) {
            return ['success' => false, 'message' => 'This coupon code already exists.'];
        }

        $err = $this->validateFields($d);
        if ($err) return ['success' => false, 'message' => $err];

        $this->coupons->insert($this->normalize($d, $code, $adminId));
        return ['success' => true];
    }

    public function update(int $id, array $d): array
    {
        $err = $this->validateFields($d);
        if ($err) return ['success' => false, 'message' => $err];

        $existing = $this->coupons->findById($id);
        $this->coupons->update($id, $this->normalize($d, $existing['code'], null, true));
        return ['success' => true];
    }

    public function delete(int $id): void
    {
        $this->coupons->delete($id);
    }

    public function toggle(int $id): void
    {
        $this->coupons->toggleStatus($id);
    }

    private function validateFields(array $d): ?string
    {
        if (empty($d['value']) || (float) $d['value'] <= 0) {
            return 'Coupon value must be greater than zero.';
        }
        if (($d['value_type'] ?? '') === 'percentage' && (float) $d['value'] > 100) {
            return 'Percentage value cannot exceed 100.';
        }
        $scope = $d['scope'] ?? 'platform';
        $needsTarget = in_array($scope, ['vendor', 'category', 'brand', 'product', 'user'], true);
        if ($needsTarget && empty($d['scope_id'])) {
            return ucfirst($scope) . ' coupon requires a target ' . $scope . ' to be selected.';
        }
        return null;
    }

    private function normalize(array $d, string $code, ?int $adminId, bool $isUpdate = false): array
    {
        $data = [
            'code'                 => $code,
            'label'                => $d['label'] ?? null,
            'value_type'           => in_array($d['value_type'] ?? '', ['fixed', 'percentage'], true) ? $d['value_type'] : 'fixed',
            'value'                => (float) $d['value'],
            'max_discount_amount'  => !empty($d['max_discount_amount']) ? (float) $d['max_discount_amount'] : null,
            'min_order_amount'     => (float) ($d['min_order_amount'] ?? 0),
            'scope'                => $d['scope'] ?? 'platform',
            'scope_id'             => !empty($d['scope_id']) ? (int) $d['scope_id'] : null,
            'usage_limit_total'    => !empty($d['usage_limit_total']) ? (int) $d['usage_limit_total'] : null,
            'usage_limit_per_user' => (int) ($d['usage_limit_per_user'] ?? 1),
            'starts_at'            => $d['starts_at'] ?: null,
            'expires_at'           => $d['expires_at'] ?: null,
            'is_active'            => (int) !empty($d['is_active']),
        ];
        if (!$isUpdate) {
            $data['created_by'] = $adminId;
        }
        return $data;
    }
}
