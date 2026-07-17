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
        $prod  = (new ProductService())->getById($pid);
        if (!$prod || $prod['status']!=='active') { $this->json(['success'=>false,'message'=>'Product unavailable.']); return; }
        $price = (float)($prod['sale_price'] ?: $prod['price']);
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
