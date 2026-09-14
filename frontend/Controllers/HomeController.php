<?php
namespace App\Frontend\Controllers;
use App\Core\Database;
use App\Frontend\Services\{ProductService, SettingsService, CartService, BannerService};

class HomeController extends FrontendController
{
    public function index(): void
    {
        $svc = new ProductService();
        // Fixed set of popular categories for the "Recommended For You"
        // tabs — a curated slice rather than every category, so the
        // section stays a quick a glance, not a repeat of "Shop by Category".
        $recommendedTabs = [
            'fashion'     => 'Fashion',
            'electronics' => 'Electronics',
            'gaming'      => 'Gaming',
            'sports'      => 'Sports',
        ];
        $recommended = [];
        foreach ($recommendedTabs as $slug => $label) {
            $products = $svc->getByCategorySlug($slug, 8);
            if (!empty($products)) $recommended[$slug] = ['label' => $label, 'products' => $products];
        }

        $this->view('home.index', [
            'title'       => SettingsService::get('site_name', 'Namma E Store') . ' — Multi-Seller Marketplace',
            'heroBanners' => (new BannerService())->getByPosition('hero'),
            'trending'    => $svc->getTrending(10),
            'newArrivals' => $svc->getNewArrivals(10),
            'flashDeals'  => $svc->getDeals(6),
            'deals'       => $svc->getDeals(4),
            'featured'    => $svc->getFeatured(6),
            'categories'  => $svc->getCategories(),
            'recommended' => $recommended,
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
        $inserted = Database::getInstance()->execute(
            "INSERT IGNORE INTO `" . DB_PREFIX . "newsletter_subscribers` (email) VALUES (?)",
            [$email]
        );
        $this->json([
            'success' => true,
            'message' => $inserted ? 'Thank you for subscribing!' : "You're already subscribed!",
        ]);
    }
}
