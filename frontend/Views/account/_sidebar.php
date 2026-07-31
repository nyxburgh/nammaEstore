<!-- Account Layout — include this at top of each account view then wrap content in .acc-main -->
<link rel="stylesheet" href="<?= asset('frontend/css/account.css') ?>">

<div class="acc-hero">
  <div class="acc-hero-inner">
    <div class="acc-hero-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
    <div><h1><?= e($title) ?></h1><p>Welcome back, <?= e($user['name']) ?>!</p></div>
  </div>
</div>

<?php $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<div class="container">
  <div class="acc-layout">
    <aside class="acc-sidebar">
      <div class="acc-sidebar-header">
        <div class="acc-av"><?= strtoupper(substr($user['name'],0,1)) ?></div>
        <div class="acc-uname"><?= e($user['name']) ?></div>
        <div class="acc-uemail"><?= e($user['email']) ?></div>
      </div>
      <ul class="acc-nav">
        <?php $links = [
          [APP_URL.'/account','👤','Overview'],
          [APP_URL.'/account/orders','📦','My Orders'],
          [APP_URL.'/account/returns','↩️','My Returns'],
          [APP_URL.'/account/wallet','👛','My Wallet'],
          [APP_URL.'/account/loyalty','⭐','Loyalty Points'],
          [APP_URL.'/account/wishlist','❤️','Wishlist'],
          [APP_URL.'/account/addresses','📍','Addresses'],
          [APP_URL.'/account/payments','💳','Payments'],
          [APP_URL.'/account/notifications','🔔','Notifications'],
          [APP_URL.'/account/reviews','⭐','My Reviews'],
          [APP_URL.'/account/profile','⚙️','Settings'],
          [APP_URL.'/logout','🚪','Logout'],
        ]; foreach($links as [$href,$ic,$lbl]):
          $isActive = rtrim($currentPath,'/') === rtrim(parse_url($href,PHP_URL_PATH),'/');
        ?>
        <li><a href="<?= $href ?>" class="<?= $isActive?'active':'' ?> <?= $lbl==='Logout'?'logout-link':'' ?>"><span class="ai"><?= $ic ?></span><?= $lbl ?></a></li>
        <?php endforeach; ?>
      </ul>
    </aside>
    <div class="acc-main">
