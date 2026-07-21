<?php
namespace App\Frontend\Services;

use App\Core\Database;

class SeoService
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function robotsTxt(): string
    {
        return <<<TXT
User-agent: *
Allow: /
Disallow: {$this->path('/mc-admin/')}
Disallow: {$this->path('/my-seller/')}
Disallow: {$this->path('/account/')}
Disallow: {$this->path('/checkout/')}
Disallow: {$this->path('/cart/')}
Disallow: {$this->path('/uploads/invoices/')}

Sitemap: {$this->path('/sitemap.xml')}
TXT;
    }

    /** Products, categories, and seller stores — the URLs actually worth indexing. */
    public function sitemapXml(): string
    {
        $urls = [
            ['loc' => APP_URL . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => APP_URL . '/products', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        $urls[] = ['loc' => APP_URL . '/info', 'priority' => '0.4', 'changefreq' => 'monthly'];
        foreach (array_keys(\App\Frontend\Controllers\PageController::PAGES) as $pageSlug) {
            $urls[] = ['loc' => APP_URL . '/info/' . $pageSlug, 'priority' => '0.4', 'changefreq' => 'monthly'];
        }

        $categories = $this->db->fetchAll("SELECT slug, updated_at FROM `" . DB_PREFIX . "categories` WHERE is_active=1");
        foreach ($categories as $c) {
            $urls[] = ['loc' => APP_URL . '/category/' . $c['slug'], 'priority' => '0.7', 'changefreq' => 'weekly'];
        }

        $products = $this->db->fetchAll("SELECT slug, updated_at FROM `" . DB_PREFIX . "products` WHERE status='active'");
        foreach ($products as $p) {
            $urls[] = ['loc' => APP_URL . '/product/' . $p['slug'], 'lastmod' => $p['updated_at'] ?? null, 'priority' => '0.8', 'changefreq' => 'weekly'];
        }

        $sellers = $this->db->fetchAll(
            "SELECT shop_slug FROM `" . DB_PREFIX . "seller_profiles` WHERE status='active' AND shop_slug IS NOT NULL"
        );
        foreach ($sellers as $v) {
            $urls[] = ['loc' => APP_URL . '/shop/' . $v['shop_slug'], 'priority' => '0.6', 'changefreq' => 'weekly'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($u['lastmod'])) $xml .= '    <lastmod>' . date('Y-m-d', strtotime($u['lastmod'])) . "</lastmod>\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    /** Path relative to the site root — matches the sub-path the app is installed under, whatever that is. */
    private function path(string $suffix): string
    {
        $base = parse_url(APP_URL, PHP_URL_PATH) ?: '';
        return rtrim($base, '/') . $suffix;
    }
}
