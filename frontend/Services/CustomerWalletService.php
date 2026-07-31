<?php
namespace App\Frontend\Services;

use App\Repositories\CustomerWalletRepository;

class CustomerWalletService
{
    private CustomerWalletRepository $wallets;

    public function __construct()
    {
        $this->wallets = new CustomerWalletRepository();
    }

    public function balance(int $userId): float
    {
        return (float) $this->wallets->getOrCreate($userId)['balance'];
    }

    public function overview(int $userId): array
    {
        return $this->wallets->getOrCreate($userId);
    }

    public function transactions(int $userId, int $page): array
    {
        return $this->wallets->getTransactions($userId, $page);
    }
}
