<?php
namespace App\Frontend\Services;

use App\Repositories\WishlistRepository;

class WishlistService
{
    private WishlistRepository $wishlist;

    public function __construct()
    {
        $this->wishlist = new WishlistRepository();
    }

    public function getByUser(int $userId, int $page = 1): array
    {
        return $this->wishlist->getByUser($userId, $page);
    }

    public function toggle(int $userId, int $productId): bool
    {
        return $this->wishlist->toggle($userId, $productId);
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        return $this->wishlist->isWishlisted($userId, $productId);
    }
}
