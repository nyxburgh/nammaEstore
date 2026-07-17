<?php include __DIR__.'/_sidebar.php'; ?>
<link rel="stylesheet" href="<?= asset('frontend/css/account.css') ?>">

<div class="card">
  <div class="card-head"><span class="card-title">🔔 Notifications</span></div>
  <div class="card-body no-pad">
    <?php if(empty($notifications['data'])): ?>
    <div class="empty-panel"><div class="empty-icon">🔔</div><p>No notifications yet.</p></div>
    <?php else: foreach($notifications['data'] as $n): ?>
    <?php $icons=['order_placed'=>'📦','order_status'=>'📦','shipment'=>'🚚','return'=>'↩️','withdrawal'=>'👛','low_stock'=>'⚠️','dispute'=>'⚖️']; ?>
    <div class="notif-item <?= $n['is_read']?'':'unread' ?>" data-action="read-notification" data-notif-id="<?= $n['id'] ?>" data-link="<?= e($n['link'] ?? '') ?>">
      <div class="notif-icon"><?= $icons[$n['type']] ?? '🔔' ?></div>
      <div class="notif-body">
        <div class="notif-title"><?= e($n['title']) ?></div>
        <div class="notif-msg"><?= e($n['message']) ?></div>
        <div class="notif-time"><?= formatDate($n['created_at'],'d M Y, h:i A') ?></div>
      </div>
      <?php if(!$n['is_read']): ?><div class="notif-dot"></div><?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
    </div></div></div>
<?php $scripts = '<script src="'.asset('frontend/js/account.js').'"></script>'; ?>
