<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\{GiftCardService, SellerService};

class GiftCardController extends AdminController
{
    private GiftCardService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new GiftCardService();
    }

    public function index(): void
    {
        Middleware::can('gift_cards');
        $f = ['search' => $this->input('search',''), 'type' => $this->input('type',''), 'status' => $this->input('status',''), 'sort' => $this->input('sort'), 'dir' => $this->input('dir')];
        $this->view('gift-cards.index', [
            'title'      => 'Gift Vouchers',
            'giftCards'  => $this->service->list((int) $this->input('page', 1), $f),
            'filters'    => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function create(): void
    {
        Middleware::can('gift_cards', 'create');
        $this->view('gift-cards.create', [
            'title'   => 'Issue Gift Voucher',
            'sellers' => (new SellerService())->options(),
        ]);
    }

    public function store(): void
    {
        Middleware::can('gift_cards', 'create'); csrf_check();
        $r = $this->service->issue($this->inputs());
        if (!$r['success']) { $this->setFlash('error', $r['message']); $this->backWithInput(); return; }
        $this->setFlash('success', 'Gift voucher issued: ' . $r['code']);
        $this->redirect(ADMIN_URL . '/gift-cards');
    }

    public function show(string $id): void
    {
        Middleware::can('gift_cards');
        $card = $this->service->find((int) $id);
        if (!$card) { $this->redirect(ADMIN_URL . '/gift-cards'); return; }
        $this->view('gift-cards.show', ['title' => $card['code'], 'card' => $card]);
    }
}
