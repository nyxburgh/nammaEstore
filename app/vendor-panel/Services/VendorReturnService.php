<?php
namespace App\VendorPanel\Services;

use App\Repositories\ReturnRepository;

class VendorReturnService
{
    private ReturnRepository $returns;

    public function __construct()
    {
        $this->returns = new ReturnRepository();
    }

    public function list(int $vendorId, int $page, array $filters): array
    {
        return $this->returns->getForVendor($vendorId, $page, $filters);
    }
}
