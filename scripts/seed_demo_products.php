<?php
/**
 * One-off script: seeds a demo vendor + demo products with generated
 * placeholder images, for local demo/testing purposes.
 *
 * Run once:  php scripts/seed_demo_products.php
 */

$host = 'localhost';
$db   = 'mycart_marketplace';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$uploadDir = __DIR__ . '/../uploads/products';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ── 1. Demo vendor ──────────────────────────────────────────────
$vendorEmail = 'vendor.demo@mycart.com';
$existing = $pdo->prepare("SELECT id FROM mc_users WHERE email = ?");
$existing->execute([$vendorEmail]);
$vendorId = $existing->fetchColumn();

if (!$vendorId) {
    $pdo->prepare("INSERT INTO mc_users (name, email, phone, password, role, is_active, is_verified, email_verified_at)
                   VALUES (?, ?, ?, ?, 'vendor', 1, 1, NOW())")
        ->execute(['Demo Vendor', $vendorEmail, '9876543210', password_hash('Vendor@123', PASSWORD_BCRYPT)]);
    $vendorId = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO mc_vendor_profiles (user_id, shop_name, shop_slug, description, status, vendor_type)
                   VALUES (?, 'Namma E Store Demo Shop', 'namma-demo-shop', 'Demo shop seeded for showcasing the marketplace.', 'active', 'commission')")
        ->execute([$vendorId]);
    echo "Created demo vendor (id=$vendorId, login: $vendorEmail / Vendor@123)\n";
} else {
    echo "Using existing demo vendor (id=$vendorId)\n";
}

// ── 2. Demo customer ──────────────────────────────────────────────
$customerEmail = 'customer.demo@mycart.com';
$existing = $pdo->prepare("SELECT id FROM mc_users WHERE email = ?");
$existing->execute([$customerEmail]);
$customerId = $existing->fetchColumn();

if (!$customerId) {
    $pdo->prepare("INSERT INTO mc_users (name, email, phone, password, role, is_active, is_verified, email_verified_at)
                   VALUES (?, ?, ?, ?, 'customer', 1, 1, NOW())")
        ->execute(['Demo Customer', $customerEmail, '9876500000', password_hash('Customer@123', PASSWORD_BCRYPT)]);
    $customerId = $pdo->lastInsertId();
    echo "Created demo customer (id=$customerId, login: $customerEmail / Customer@123)\n";
} else {
    echo "Using existing demo customer (id=$customerId)\n";
}

// ── 3. Category id lookup ───────────────────────────────────────
$catRows = $pdo->query("SELECT id, slug FROM mc_categories")->fetchAll(PDO::FETCH_KEY_PAIR);
$catIds = array_flip($catRows); // slug => id

// ── 4. Demo products ─────────────────────────────────────────────
// [category, name, price, sale_price, stock, image search keyword, featured]
$products = [
    ['electronics', 'Wireless Bluetooth Headphones', 1999.00, 1599.00, 40, 'headphones', true],
    ['electronics', 'Smart Fitness Band', 1499.00, null, 60, 'fitness-band,smartwatch', false],
    ['fashion', "Men's Casual Denim Jacket", 2199.00, 1799.00, 25, 'denim-jacket,mensfashion', true],
    ['fashion', "Women's Floral Summer Dress", 1299.00, null, 30, 'summerdress,womensfashion', false],
    ['home-kitchen', 'Stainless Steel Cookware Set (5 Pcs)', 3499.00, 2999.00, 15, 'cookware,kitchenware', true],
    ['home-kitchen', 'Electric Kettle 1.8L', 899.00, null, 50, 'electrickettle', false],
    ['sports', 'Yoga Mat with Carry Strap', 699.00, 549.00, 80, 'yogamat', false],
    ['sports', 'Adjustable Dumbbell Set 10kg', 2499.00, null, 20, 'dumbbell,gym', false],
    ['books', 'The Art of Clean Code (Paperback)', 499.00, 399.00, 100, 'books,bookstack', false],
    ['beauty', 'Herbal Face Wash Combo Pack', 399.00, null, 70, 'skincare,facewash', false],
    ['grocery', 'Organic Assorted Dry Fruits Box 500g', 649.00, 599.00, 45, 'dryfruits,nuts', true],
    ['gaming', 'Wireless Gaming Mouse RGB', 1199.00, 999.00, 35, 'gamingmouse', false],
];

function slugify(string $s): string {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

// Downloads a real photo matched to $keywords (comma-separated Flickr tags)
// via LoremFlickr. Falls back to a plain colored placeholder if offline.
function downloadProductImage(string $path, string $keywords, string $title): void {
    $url = 'https://loremflickr.com/800/800/' . rawurlencode($keywords);
    $ctx = stream_context_create(['http' => ['timeout' => 12]]);
    $data = @file_get_contents($url, false, $ctx);

    if ($data !== false && strlen($data) > 1000) {
        file_put_contents($path, $data);
        return;
    }

    // offline fallback: simple gray placeholder so the seed never hard-fails
    $im = imagecreatetruecolor(800, 800);
    $bg = imagecolorallocate($im, 200, 200, 200);
    imagefill($im, 0, 0, $bg);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagestring($im, 5, 20, 380, substr($title, 0, 40), $white);
    imagepng($im, $path, 6);
    imagedestroy($im);
}

$insertProduct = $pdo->prepare(
    "INSERT INTO mc_products
        (vendor_id, category_id, name, slug, sku, description, short_description,
         price, sale_price, stock, status, is_featured, gst_rate, approved_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 18.00, NOW())"
);
$insertImage = $pdo->prepare(
    "INSERT INTO mc_product_images (product_id, image_path, alt_text, is_primary, sort_order)
     VALUES (?, ?, ?, 1, 0)"
);

$created = 0;
foreach ($products as [$catSlug, $name, $price, $salePrice, $stock, $keywords, $featured]) {
    $slug = slugify($name);

    $exists = $pdo->prepare("SELECT id FROM mc_products WHERE slug = ?");
    $exists->execute([$slug]);
    if ($exists->fetchColumn()) {
        echo "Skip (exists): $name\n";
        continue;
    }

    $categoryId = $catIds[$catSlug] ?? null;
    $sku = 'DEMO-' . strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($slug)), 0, 6)) . '-' . rand(100, 999);

    $imgFile = $slug . '.jpg';
    $imgPath = 'products/' . $imgFile;
    echo "Downloading image for: $name ($keywords)\n";
    downloadProductImage($uploadDir . '/' . $imgFile, $keywords, $name);

    $shortDesc = "Great quality $name, perfect for everyday use. Demo product for showcase purposes.";
    $desc = "$name\n\nThis is a demo product added to showcase the Namma E Store marketplace catalog. "
          . "Features premium quality, fast shipping, and easy returns. Category: "
          . ucwords(str_replace('-', ' ', $catSlug)) . ".";

    $insertProduct->execute([
        $vendorId, $categoryId, $name, $slug, $sku, $desc, $shortDesc,
        $price, $salePrice, $stock, $featured ? 1 : 0,
    ]);
    $productId = $pdo->lastInsertId();

    $insertImage->execute([$productId, $imgPath, $name]);

    echo "Created product #$productId: $name\n";
    $created++;
}

echo "\nDone. $created new product(s) created.\n";
