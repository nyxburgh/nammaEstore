<?php
namespace App\Admin\Services;

use App\Repositories\ReviewRepository;

class ReviewService
{
    private ReviewRepository $reviews;

    public function __construct()
    {
        $this->reviews = new ReviewRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->reviews->getForModeration($page, $filters);
    }

    public function stats(): array
    {
        return $this->reviews->getModerationStats();
    }

    public function approve(int $id): void
    {
        $this->reviews->approve($id);
    }

    public function reject(int $id): void
    {
        $this->reviews->reject($id);
    }

    public function delete(int $id): void
    {
        $this->reviews->delete($id);
    }
}
