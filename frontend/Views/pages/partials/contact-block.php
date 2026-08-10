<?php
use App\Frontend\Services\SettingsService;
$sEmail    = SettingsService::get('site_email', 'info@nammaestore.com');
$sPhone    = SettingsService::get('site_phone', '+91 9999999999');
$sPhoneRaw = preg_replace('/[^0-9]/', '', $sPhone);
$gmailComposeUrl = 'https://mail.google.com/mail/?view=cm&fs=1&to=' . rawurlencode($sEmail) . '&su=' . rawurlencode('Query for ' . SettingsService::get('site_name', 'Namma E Store'));
$whatsappUrl     = 'https://wa.me/' . $sPhoneRaw;
?>
<div class="contact-grid">
  <div class="contact-info-box">
    <div>📧 <strong>Email:</strong> <a href="<?= e($gmailComposeUrl) ?>" target="_blank" rel="noopener"><?= e($sEmail) ?></a></div>
    <div>📱 <strong>Call:</strong> <a href="tel:+<?= e($sPhoneRaw) ?>"><?= e($sPhone) ?></a> &nbsp;|&nbsp; <strong>WhatsApp:</strong> <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener">Message us</a></div>
    <div>🕘 <strong>Support hours:</strong> Mon–Sat, 9:00 AM – 8:00 PM IST</div>
    <div>📦 <strong>Order help:</strong> <a href="<?= APP_URL ?>/track">Track your order</a></div>
    <div>🏪 <strong>Seller support:</strong> <a href="<?= SELLER_URL ?>">Seller Panel</a></div>
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
