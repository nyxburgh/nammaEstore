<?php $siteName = e(\App\Frontend\Services\SettingsService::get('site_name','Namma E Store')); ?>
<p>Welcome to <?= $siteName ?>. By accessing or purchasing on this website, you agree to the terms below. Please read them carefully.</p>

<h2>1. About the marketplace</h2>
<p><?= $siteName ?> is a multi-seller marketplace. Products are listed and sold by independent sellers; <?= $siteName ?> facilitates the transaction, payment and dispute resolution between you and the seller.</p>

<h2>2. Your account</h2>
<ul>
  <li>You must provide accurate information when registering and keep your credentials confidential.</li>
  <li>You are responsible for all activity under your account.</li>
  <li>Accounts engaging in fraud, abuse or misuse of offers may be suspended.</li>
</ul>

<h2>3. Orders, pricing &amp; availability</h2>
<ul>
  <li>All prices are in Indian Rupees (₹) and include GST unless stated otherwise.</li>
  <li>An order is confirmed only after successful payment (or COD confirmation).</li>
  <li>In rare cases of stock errors or obvious pricing mistakes, we may cancel the order with a full refund.</li>
</ul>

<h2>4. Delivery</h2>
<p>Delivery timelines shown at checkout are estimates. Risk in the goods passes to you on delivery. See our <a href="<?= APP_URL ?>/info/shipping-policy">Shipping Policy</a>.</p>

<h2>5. Returns &amp; refunds</h2>
<p>Returns are governed by our <a href="<?= APP_URL ?>/info/return-policy">Return Policy</a> and <a href="<?= APP_URL ?>/info/cancellation-refund">Cancellation &amp; Refund Policy</a>.</p>

<h2>6. Reviews &amp; content</h2>
<p>Reviews must be genuine and respectful. We may remove content that is abusive, misleading, or violates any law. By posting a review you grant us the right to display it on the platform.</p>

<h2>7. Intellectual property</h2>
<p>All site content — logo, design, text and images (except seller product images) — belongs to <?= $siteName ?> and may not be copied without permission.</p>

<h2>8. Limitation of liability</h2>
<p>To the maximum extent permitted by law, our liability for any claim related to an order is limited to the amount you paid for that order.</p>

<h2>9. Governing law</h2>
<p>These terms are governed by the laws of India. Disputes are subject to the exclusive jurisdiction of the courts at the company's registered office location.</p>

<p><em>Last updated: July 2026.</em></p>
