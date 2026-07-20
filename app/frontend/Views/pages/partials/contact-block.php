<?php
use App\Frontend\Services\SettingsService;
$sEmail = e(SettingsService::get('site_email', 'info@nammaestore.com'));
$sPhone = e(SettingsService::get('site_phone', '+91 9999999999'));
?>
<div class="contact-grid">
  <div class="contact-info-box">
    <div>📧 <strong>Email:</strong> <a href="mailto:<?= $sEmail ?>"><?= $sEmail ?></a></div>
    <div>📱 <strong>Phone / WhatsApp:</strong> <a href="tel:<?= str_replace(' ','',$sPhone) ?>"><?= $sPhone ?></a></div>
    <div>🕘 <strong>Support hours:</strong> Mon–Sat, 9:00 AM – 8:00 PM IST</div>
    <div>📦 <strong>Order help:</strong> <a href="<?= APP_URL ?>/track">Track your order</a></div>
    <div>🏪 <strong>Seller support:</strong> <a href="<?= VENDOR_URL ?>">Vendor Panel</a></div>
  </div>

  <form class="contact-form" method="POST" action="<?= APP_URL ?>/info/contact-us" onsubmit="return validateForm(this)">
    <?= csrf_field() ?>
    <label for="cf-name">Your Name</label>
    <input id="cf-name" type="text" name="name" required maxlength="100" placeholder="Full name" oninput="validateField(this)" onblur="validateField(this)">
    <label for="cf-email">Email</label>
    <input id="cf-email" type="email" name="email" required maxlength="150" placeholder="you@example.com" oninput="validateField(this)" onblur="validateField(this)">
    <label for="cf-msg">Message</label>
    <textarea id="cf-msg" name="message" required maxlength="2000" placeholder="How can we help?" oninput="validateField(this)" onblur="validateField(this)"></textarea>
    <button type="submit">Send Message →</button>
  </form>
</div>
