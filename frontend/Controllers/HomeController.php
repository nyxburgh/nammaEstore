<?php
namespace App\Frontend\Controllers;
use App\Frontend\Services\{ProductService, SettingsService, CartService};

class HomeController extends FrontendController
{
    public function index(): void
    {
        $svc = new ProductService();
        $this->view('home.index', [
            'title'       => SettingsService::get('site_name', 'Namma E Store') . ' — Multi-Seller Marketplace',
            'trending'    => $svc->getTrending(10),
            'newArrivals' => $svc->getNewArrivals(10),
            'deals'       => $svc->getDeals(4),
            'featured'    => $svc->getFeatured(6),
            'categories'  => $svc->getCategories(),
            'cartCount'   => (new CartService())->getCount(),
            'settings'    => SettingsService::all(),
        ]);
    }

    public function subscribe(): void
    {
        csrf_check();
        $email = filter_var($this->input('email', ''), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Invalid email address.']);
            return;
        }
        $this->json(['success' => true, 'message' => 'Thank you for subscribing!']);
    }
}
