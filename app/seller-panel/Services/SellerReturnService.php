<?php
namespace App\SellerPanel\Services;

use App\Repositories\ReturnRepository;

class SellerReturnService
{
    private ReturnRepository $returns;

    public function __construct()
    {
        $this->returns = new ReturnRepository();
    }

    public function list(int $sellerId, int $page, array $filters): array
    {
        return $this->returns->getForSeller($sellerId, $page, $filters);
    }
}
