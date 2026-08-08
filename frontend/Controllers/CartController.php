<?php
namespace App\Frontend\Controllers;
use App\Core\Auth;
use App\Frontend\Services\{CartService, ProductService, SettingsService, WishlistService};

class CartController extends FrontendController
{
    public function index(): void {
        $cart = new CartService();
        $this->view('cart.index', [
            'title'    => 'Your Cart',
            'summary'  => $cart->getSummary(),
            'cartCount'=> $cart->getCount(),
            'settings' => SettingsService::all(),
            'categories'=> (new ProductService())->getCategories(),
        ]);
    }
    public function add(): void {
        csrf_check();
        $pid   = (int)$this->input('product_id');
        $qty   = max(1,(int)$this->input('qty',1));
        $vid   = $this->input('variant_id') ? (int)$this->input('variant_id') : null;
        $productSvc = new ProductService();
        $prod  = $productSvc->getById($pid);
        if (!$prod || $prod['status']!=='active') { $this->json(['success'=>false,'message'=>'Product unavailable.']); return; }
        $price = (float)($prod['sale_price'] ?: $prod['price']);
        if ($vid) {
            $variant = $productSvc->getVariant($vid, $pid);
            if (!$variant) { $this->json(['success'=>false,'message'=>'Selected option is no longer available.']); return; }
            $price += (float)$variant['price_modifier'];
        }
        $r     = (new CartService())->add($pid, $qty, $vid, $price);
        $this->json($r);
    }
    public function remove(): void {
        csrf_check();
        $r = (new CartService())->remove((int)$this->input('item_id'));
        $this->json($r);
    }
    public function update(): void {
        csrf_check();
        $r = (new CartService())->update((int)$this->input('item_id'), (int)$this->input('qty'));
        $this->json($r);
    }
    public function count(): void {
        $this->json(['count' => (new CartService())->getCount()]);
    }
    public function mini(): void {
        $summary = (new CartService())->getSummary();
        $items = array_map(function ($item) {
            $price = (float)($item['sale_price'] ?: $item['price']) + (float)($item['price_modifier'] ?? 0);
            $qty   = (int)$item['quantity'];
            return [
                'id'        => (int)$item['id'],
                'name'      => $item['name'],
                'seller'    => $item['shop_name'] ?: $item['seller_name'],
                'image'     => !empty($item['image']) ? UPLOAD_URL . '/' . $item['image'] : null,
                'variant'   => !empty($item['variant_value']) ? ($item['variant_name'] . ': ' . $item['variant_value']) : null,
                'qty'       => $qty,
                'lineTotal' => $price * $qty,
            ];
        }, $summary['items']);
        $this->json([
            'count' => array_sum(array_column($items, 'qty')),
            'items' => $items,
            'total' => $summary['total'],
        ]);
    }
    public function clear(): void {
        csrf_check();
        (new CartService())->clear();
        $this->json(['success'=>true]);
    }
    public function toggleWishlist(): void {
        csrf_check();
        if (!Auth::isUserLoggedIn()) { $this->json(['success'=>false,'message'=>'Login required.','redirect'=>APP_URL.'/login']); return; }
        $added = (new WishlistService())->toggle(Auth::userId(), (int)$this->input('product_id'));
        $this->json(['success'=>true,'added'=>$added]);
    }
}
