<?php
namespace App\Frontend\Controllers;
use App\Frontend\Services\PwaService;

class PwaController extends FrontendController
{
    public function manifest(): void
    {
        header('Content-Type: application/manifest+json');
        echo (new PwaService())->manifestJson();
    }

    /**
     * Service workers must be served from the root scope they intend
     * to control — routed here (rather than a static file under
     * assets/) so /sw.js resolves at the site root regardless of
     * whether the app sits at a domain root or a subpath.
     */
    public function serviceWorker(): void
    {
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');
        echo (new PwaService())->serviceWorkerJs();
    }
}
