<?php
namespace App\Admin\Services;

use App\Repositories\PaymentRepository;

class PaymentService
{
    private PaymentRepository $payments;

    public function __construct()
    {
        $this->payments = new PaymentRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->payments->getPayments($page, $filters);
    }

    public function totals(): array
    {
        return $this->payments->getTotals();
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'success', 'failed', 'refunded'], true)) {
            return false;
        }
        $this->payments->updateStatus($id, $status);
        return true;
    }
}
