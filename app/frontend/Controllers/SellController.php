<?php
namespace App\Frontend\Controllers;

use App\Core\Database;
use App\Frontend\Services\{CartService, ProductService, SettingsService};

class SellController extends FrontendController
{
    /** "Become a Seller" marketing landing page — links out to the vendor panel at VENDOR_URL. */
    public function index(): void
    {
        $db = Database::getInstance();

        $vendorCount = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS c FROM `mc_vendor_profiles` WHERE status = 'active'"
        )['c'] ?? 0);

        $productCount = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS c FROM `mc_products` WHERE status = 'active'"
        )['c'] ?? 0);

        $plans = $db->fetchAll(
            "SELECT name, price, commission_pct, free_threshold, commission_after_threshold, billing_cycle, description
             FROM `mc_subscription_plans` WHERE is_active = 1 ORDER BY sort_order ASC"
        );

        $siteName = SettingsService::get('site_name', 'Namma E Store');

        $this->view('sell.landing', [
            'title'           => 'Sell on ' . $siteName . ' — Start Your Online Store',
            'metaDescription' => 'Register as a seller on ' . $siteName . ' and reach thousands of customers. Low commission, fast payouts, and a full seller dashboard.',
            'cartCount'       => (new CartService())->getCount(),
            'categories'      => (new ProductService())->getCategories(),
            'settings'        => SettingsService::all(),
            'vendorCount'     => $vendorCount,
            'productCount'    => $productCount,
            'plans'           => $plans,
            'siteName'        => $siteName,
        ]);
    }
}
