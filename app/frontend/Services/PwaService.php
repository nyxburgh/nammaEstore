<?php
namespace App\Frontend\Services;

class PwaService
{
    public function manifestJson(): string
    {
        $siteName = SettingsService::get('site_name', 'Namma E Store');
        $manifest = [
            'name'             => $siteName,
            'short_name'       => $siteName,
            'description'      => 'Shop from thousands of trusted vendors on ' . $siteName . '.',
            'start_url'        => APP_URL . '/',
            'scope'            => APP_URL . '/',
            'display'          => 'standalone',
            'background_color' => '#f8f4f8',
            'theme_color'      => '#e91e8c',
            'orientation'      => 'portrait-primary',
            'icons'            => [
                ['src' => FRONTEND_ASSETS . '/images/pwa-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => FRONTEND_ASSETS . '/images/pwa-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ];
        return json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function serviceWorkerJs(): string
    {
        // Cache-first for static assets, network-first for everything
        // else — a marketplace's prices/stock must never be served
        // stale, but CSS/JS/images are safe (and valuable) to cache.
        $cacheName = 'nammaestore-v2';
        return <<<JS
const CACHE_NAME = '{$cacheName}';
const STATIC_ASSET_PATTERN = /\\.(css|js|woff2?|png|jpe?g|webp|svg)(\\?.*)?$/;

self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;

  if (STATIC_ASSET_PATTERN.test(new URL(req.url).pathname)) {
    event.respondWith(
      caches.match(req).then(cached => cached || fetch(req).then(res => {
        const clone = res.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(req, clone));
        return res;
      }))
    );
    return;
  }

  // Network-first for everything else (pages, prices, cart, API calls)
  event.respondWith(
    fetch(req).catch(() => caches.match(req))
  );
});
JS;
    }
}
