<?php
namespace App\Frontend\Controllers;

use App\Frontend\Services\{CartService, PageService, ProductService, SettingsService};

class PageController extends FrontendController
{
    /** Registry of all static info pages: slug => [emoji icon, title, short description] */
    public const PAGES = [
        'about-us'            => ['🏬', 'About Us',             'Who we are and what we stand for'],
        'contact-us'          => ['📞', 'Contact Us',           'Reach our support team anytime'],
        'faq'                 => ['❓', 'FAQ',                  'Answers to common questions'],
        'shipping-policy'     => ['🚚', 'Shipping Policy',      'Delivery timelines and charges'],
        'return-policy'       => ['🔄', 'Return Policy',        'How returns and replacements work'],
        'cancellation-refund' => ['💰', 'Cancellation & Refund','Cancelling orders and getting refunds'],
        'payment-methods'     => ['💳', 'Payment Methods',      'Ways you can pay securely'],
        'privacy-policy'      => ['🔒', 'Privacy Policy',       'How we handle your data'],
        'terms-conditions'    => ['📜', 'Terms & Conditions',   'Rules for using our marketplace'],
        'disclaimer'          => ['⚠️', 'Disclaimer',           'Important legal information'],
    ];

    private function common(): array {
        return [
            'cartCount'  => (new CartService())->getCount(),
            'categories' => (new ProductService())->getCategories(),
            'settings'   => SettingsService::all(),
        ];
    }

    /** BreadcrumbList JSON-LD for SEO */
    private function breadcrumbJsonLd(array $crumbs): string {
        $items = [];
        foreach ($crumbs as $i => [$name, $url]) {
            $item = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name];
            if ($url) $item['item'] = $url;
            $items[] = $item;
        }
        return '<script type="application/ld+json">' . json_encode([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ], JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /** Info Center — all pages as cards */
    public function index(): void {
        $siteName = SettingsService::get('site_name', 'Namma E Store');
        $this->view('pages.index', $this->common() + [
            'title'           => 'Info Center — ' . $siteName,
            'metaDescription' => 'Help and information hub for ' . $siteName . ': policies, shipping, returns, payments, FAQ and contact details.',
            'pages'           => PageService::all(),
            'structuredData'  => $this->breadcrumbJsonLd([
                ['Home', APP_URL . '/'],
                ['Info Center', null],
            ]),
        ]);
    }

    /** Single info page with breadcrumb */
    public function show(string $slug): void {
        $page = PageService::find($slug);
        if (!$page) {
            http_response_code(404);
            $this->view('errors.404', $this->common() + ['title' => 'Page Not Found']);
            return;
        }
        $pageTitle   = $page['title'];
        $pageSub     = $page['short_description'] ?? '';
        $siteName    = SettingsService::get('site_name', 'Namma E Store');
        $contentView = FRONTEND_VIEWS . '/pages/content/' . $slug . '.php';
        $this->view('pages.show', $this->common() + [
            'title'           => $pageTitle . ' — ' . $siteName,
            'metaDescription' => $pageTitle . ' | ' . $pageSub . ' — ' . $siteName . '.',
            'pageSlug'        => $slug,
            'pageIcon'        => $page['icon'],
            'pageTitle'       => $pageTitle,
            // DB content wins; the file view is the pre-migration fallback
            'pageContent'     => ($page['content'] ?? '') !== '' ? PageService::render($page['content']) : null,
            'contentView'     => is_file($contentView) ? $contentView : null,
            'structuredData'  => $this->breadcrumbJsonLd([
                ['Home', APP_URL . '/'],
                ['Info Center', APP_URL . '/info'],
                [$pageTitle, null],
            ]),
        ]);
    }

    /** Contact form submission */
    public function contactSubmit(): void {
        csrf_check();
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
            $this->setFlash('error', 'Please fill in your name, a valid email and a message.');
        } else {
            error_log(sprintf('[contact-form] %s <%s>: %s', $name, $email, mb_substr($message, 0, 500)));
            $this->setFlash('success', 'Thank you! Your message has been received — we will get back to you soon.');
        }
        $this->redirect(APP_URL . '/info/contact-us');
    }
}
