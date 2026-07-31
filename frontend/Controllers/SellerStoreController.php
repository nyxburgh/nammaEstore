<?php
namespace App\Frontend\Controllers;
use App\Frontend\Services\{ProductService, CartService, SettingsService};

class SellerStoreController extends FrontendController
{
    public function show(string $slug): void
    {
        $svc  = new ProductService();
        $shop = $svc->getSellerStoreBySlug($slug);

        if (!$shop) {
            http_response_code(404);
            include FRONTEND_VIEWS . '/errors/404.php';
            exit;
        }

        $page    = (int)($this->input('page', 1));
        $filters = [
            'sort'      => $this->input('sort', 'newest'),
            'category'  => $this->input('category'),
            'min_price' => $this->input('min_price'),
            'max_price' => $this->input('max_price'),
            'seller_id' => (int)$shop['user_id'],
        ];

        $this->view('seller.store', [
            'title'      => e($shop['shop_name']) . ' — ' . SettingsService::get('site_name', 'Namma E Store'),
            'shop'       => $shop,
            'products'   => $svc->getAll($page, $filters),
            'categories' => $svc->getCategories(),
            'filters'    => $filters,
            'cartCount'  => (new CartService())->getCount(),
            'settings'   => SettingsService::all(),
        ]);
    }
}
