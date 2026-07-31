<?php $siteName = e(\App\Frontend\Services\SettingsService::get('site_name','Namma E Store')); ?>
<h2>Marketplace disclaimer</h2>
<p><?= $siteName ?> is an intermediary platform. Product listings — including descriptions, images, pricing and stock — are provided by independent sellers. While we verify sellers and monitor listings, we do not manufacture the products and cannot guarantee that every description is complete or error-free.</p>

<h2>Product information</h2>
<ul>
  <li>Colours may vary slightly from the images due to screen settings and photography.</li>
  <li>Packaging may differ from what is shown if the manufacturer updates it.</li>
  <li>For food, cosmetics and healthcare items, always read the label, ingredients and directions on the actual product before use.</li>
</ul>

<h2>Pricing &amp; offers</h2>
<p>Discounts, coupons and flash-sale prices are time-bound and may change or be withdrawn without notice. In case of an obvious pricing error, the order may be cancelled with a full refund.</p>

<h2>External links</h2>
<p>Pages may contain links to third-party sites (courier tracking, payment gateways). We are not responsible for the content or privacy practices of those sites.</p>

<h2>No professional advice</h2>
<p>Nothing on this site constitutes medical, legal or financial advice. Consult a qualified professional before acting on any product claim.</p>

<p>If you find an incorrect listing or have a concern about a product, please report it via our <a href="<?= APP_URL ?>/info/contact-us">Contact Us</a> page.</p>
