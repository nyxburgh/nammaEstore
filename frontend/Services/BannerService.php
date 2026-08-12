<?php
namespace App\Frontend\Services;

use App\Repositories\BannerRepository;

class BannerService
{
    private BannerRepository $banners;

    public function __construct()
    {
        $this->banners = new BannerRepository();
    }

    public function getByPosition(string $position): array
    {
        return $this->banners->getActiveByPosition($position);
    }
}
