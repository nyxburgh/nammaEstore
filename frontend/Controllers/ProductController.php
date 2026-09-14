<?php
namespace App\Frontend\Controllers;
use App\Core\Auth;
use App\Frontend\Services\{ProductService, CartService, SettingsService, ReviewService, WishlistService};
use App\Repositories\StockNotificationRepository;

class ProductController extends FrontendController
{
    public function index(): void
    {
        $svc     = new ProductService();
        $filters = [
            'sort'       => $this->input('sort', 'newest'),
            'category'   => $this->input('category'),
            'categories' => array_filter((array)($_GET['categories'] ?? [])),
            'min_price'  => $this->input('min_price'),
            'max_price'  => $this->input('max_price'),
        ];

        $this->view('products.index', [
            'title'      => 'All Products — ' . SettingsService::get('site_name', 'Namma E Store'),
            'products'   => $svc->getAll((int)$this->input('page', 1), $filters),
            'categories' => $svc->getCategories(),
            'filters'    => $filters,
            'cartCount'  => (new CartService())->getCount(),
            'settings'   => SettingsService::all(),
        ]);
    }

    public function category(string $slug): void
    {
        $svc     = new ProductService();
        $filters = [
            'sort'       => $this->input('sort', 'newest'),
            'categories' => array_filter((array)($_GET['categories'] ?? [])),
            'min_price'  => $this->input('min_price'),
            'max_price'  => $this->input('max_price'),
        ];
        $result  = $svc->getByCategory($slug, (int)$this->input('page', 1), $filters);

        if (!$result['category']) {
            http_response_code(404);
            include FRONTEND_VIEWS . '/errors/404.php';
            exit;
        }

        $this->view('products.index', [
            'title'      => e($result['category']['name']) . ' — ' . SettingsService::get('site_name', 'Namma E Store'),
            'products'   => $result['products'],
            'category'   => $result['category'],
            'categories' => $svc->getCategories(),
            'filters'    => $filters,
            'cartCount'  => (new CartService())->getCount(),
            'settings'   => SettingsService::all(),
        ]);
    }

    public function search(): void
    {
        $q       = trim($this->input('q', ''));
        $svc     = new ProductService();
        $filters = [
            'sort'       => $this->input('sort', 'popular'),
            'category'   => $this->input('category'),
            'categories' => array_filter((array)($_GET['categories'] ?? [])),
            'min_price'  => $this->input('min_price'),
            'max_price'  => $this->input('max_price'),
        ];

        // Reached with a category picked from the header search bar's
        // "All Categories" dropdown but no typed keyword — resolve the
        // category name so the page heading can show it instead of the
        // empty Search: "" that a blank $q would otherwise produce.
        $searchCategory = !empty($filters['category']) ? $svc->getCategoryById((int) $filters['category']) : null;
        $headingLabel = $q !== ''
            ? 'Search: "' . $q . '"'
            : ($searchCategory ? '"' . $searchCategory['name'] . '"' : '"All Products"');

        $this->view('products.index', [
            'title'      => ($q !== '' ? 'Search: "' . e($q) . '"' : ($searchCategory ? e($searchCategory['name']) : 'All Products'))
                            . ' — ' . SettingsService::get('site_name', 'Namma E Store'),
            'products'   => $q !== ''
                            ? $svc->search($q, (int)$this->input('page', 1), $filters)
                            : $svc->getAll((int)$this->input('page', 1), $filters),
            'categories' => $svc->getCategories(),
            'filters'    => $filters,
            'searchQ'    => $q,
            'searchCategory' => $searchCategory,
            'headingLabel'   => $headingLabel,
            'cartCount'  => (new CartService())->getCount(),
            'settings'   => SettingsService::all(),
        ]);
    }

    public function suggest(): void
    {
        $q = trim($this->input('q', ''));
        if (mb_strlen($q) < 2) {
            $this->json(['items' => []]);
            return;
        }
        $items = (new ProductService())->suggest($q, 8);
        $this->json([
            'items' => array_map(fn($p) => [
                'name'  => $p['name'],
                'slug'  => $p['slug'],
                'price' => (float) ($p['sale_price'] ?: $p['price']),
                'image' => !empty($p['image']) ? UPLOAD_URL . '/' . $p['image'] : null,
            ], $items),
        ]);
    }

    public function show(string $slug): void
    {
        $svc     = new ProductService();
        $product = $svc->getBySlug($slug);

        if (!$product) {
            http_response_code(404);
            include FRONTEND_VIEWS . '/errors/404.php';
            exit;
        }

        $reviewSvc     = new ReviewService();
        $isWishlisted = Auth::userId()
                        ? (new WishlistService())->isWishlisted(Auth::userId(), $product['id'])
                        : false;
        $canReview = Auth::userId() ? !$reviewSvc->hasReviewed(Auth::userId(), $product['id']) : false;
        $myReview  = Auth::userId() ? $reviewSvc->getMyReview(Auth::userId(), $product['id']) : null;
        $reviewStats = $reviewSvc->getStats($product['id']);
        $price = (float) ($product['sale_price'] ?: $product['price']);
        $productImage = !empty($product['images'][0]['image_path'])
            ? UPLOAD_URL . '/' . $product['images'][0]['image_path']
            : (FRONTEND_ASSETS . '/images/og-default.jpg');

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'],
            'image'       => [$productImage],
            'description' => strip_tags($product['description'] ?? ''),
            'sku'         => $product['sku'] ?? null,
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => APP_URL . '/product/' . $product['slug'],
                'priceCurrency' => 'INR',
                'price'         => $price,
                'availability'  => (int) $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ];
        if ((int) $reviewStats['total'] > 0) {
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => round((float) $reviewStats['avg_rating'], 1),
                'reviewCount' => (int) $reviewStats['total'],
            ];
        }

        $this->view('products.show', [
            'title'          => e($product['name']) . ' — ' . SettingsService::get('site_name', 'Namma E Store'),
            'metaDescription'=> strip_tags($product['description'] ?? '') ?: ($product['name'] . ' — available now on ' . SettingsService::get('site_name', 'Namma E Store')),
            'metaImage'      => $productImage,
            'ogType'         => 'product',
            'structuredData' => '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>',
            'product'      => $product,
            'related'      => $svc->getRelated($product['id'], (int)($product['category_id'] ?? 0), 6),
            'reviews'      => $reviewSvc->getByProduct($product['id']),
            'reviewStats'  => $reviewStats,
            'isWishlisted' => $isWishlisted,
            'canReview'    => $canReview,
            'myReview'     => $myReview,
            'categories'   => $svc->getCategories(),
            'cartCount'    => (new CartService())->getCount(),
            'settings'     => SettingsService::all(),
        ]);
    }

    /** "Remind Me Later" on an out-of-stock product — registers interest; the actual notification fires when stock is restocked (see SellerPanel ProductController::update()). */
    public function notifyRestock(string $id): void
    {
        csrf_check();
        if (!Auth::isUserLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Please login to get notified.', 'requireLogin' => true], 401);
            return;
        }
        $isNew = (new StockNotificationRepository())->subscribe(Auth::userId(), (int) $id);
        $this->json([
            'success' => true,
            'message' => $isNew
                ? "We'll email you as soon as this product is back in stock!"
                : "You're already on the list for this product.",
        ]);
    }

    public function submitReview(string $id): void
    {
        csrf_check();
        if (!Auth::isUserLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Login required.'], 401);
            return;
        }
        $reviewSvc = new ReviewService();
        $r = $reviewSvc->submit(
            Auth::userId(),
            (int) $id,
            (int) $this->input('rating', 5),
            $this->input('title', ''),
            $this->input('body', '')
        );
        $this->json($r);
    }
}
