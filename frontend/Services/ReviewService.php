<?php
namespace App\Frontend\Services;

use App\Repositories\ReviewRepository;

class ReviewService
{
    private ReviewRepository $reviews;

    public function __construct()
    {
        $this->reviews = new ReviewRepository();
    }

    public function getByProduct(int $productId, int $page = 1): array
    {
        return $this->reviews->getByProduct($productId, $page);
    }

    public function getStats(int $productId): array
    {
        return $this->reviews->getStats($productId);
    }

    public function getByUser(int $userId, int $page = 1): array
    {
        return $this->reviews->getByUser($userId, $page);
    }

    public function hasReviewed(int $userId, int $productId): bool
    {
        return $this->reviews->hasReviewed($userId, $productId);
    }

    public function submit(int $userId, int $productId, int $rating, string $title, string $body): array
    {
        if ($this->reviews->hasReviewed($userId, $productId)) {
            return ['success' => false, 'message' => 'You have already reviewed this product.'];
        }
        $this->reviews->create([
            'product_id'  => $productId,
            'user_id'     => $userId,
            'rating'      => max(1, min(5, $rating)),
            'title'       => $title,
            'body'        => $body,
            'is_approved' => 0,
        ]);
        return ['success' => true, 'message' => 'Review submitted for approval. Thank you!'];
    }
}
