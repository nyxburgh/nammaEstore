<?php
namespace App\Frontend\Controllers;
use App\Frontend\Services\SeoService;

class SeoController extends FrontendController
{
    public function robots(): void
    {
        header('Content-Type: text/plain');
        echo (new SeoService())->robotsTxt();
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml');
        echo (new SeoService())->sitemapXml();
    }
}
