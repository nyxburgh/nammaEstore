<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\ReviewService;

class ReviewController extends AdminController
{
    private ReviewService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new ReviewService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $f = [
            'status'  => $this->input('status', ''),
            'search'  => $this->input('search', ''),
            'rating'  => $this->input('rating', ''),
            'sort'    => $this->input('sort'),
            'dir'     => $this->input('dir'),
        ];

        $this->view('reviews.index', [
            'title'   => 'Reviews',
            'reviews' => $this->service->list((int)$this->input('page', 1), $f),
            'filters' => $f,
            'stats'   => $this->service->stats(),
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function approve(string $id): void
    {
        Middleware::adminAuth(); Middleware::can('products', 'edit'); csrf_check();
        $this->service->approve((int)$id);
        $this->json(['success' => true]);
    }

    public function reject(string $id): void
    {
        Middleware::adminAuth(); Middleware::can('products', 'edit'); csrf_check();
        $this->service->reject((int)$id);
        $this->json(['success' => true]);
    }

    public function delete(string $id): void
    {
        Middleware::adminAuth(); Middleware::can('products', 'delete'); csrf_check();
        $this->service->delete((int)$id);
        $this->json(['success' => true]);
    }
}
