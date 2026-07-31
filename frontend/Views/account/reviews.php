<?php include __DIR__.'/_sidebar.php'; ?>
<div class="card">
  <div class="card-head"><span class="card-title">⭐ My Reviews</span><span class="head-count"><?= $reviews['total'] ?> review<?= $reviews['total']!=1?'s':'' ?></span></div>
  <div class="card-body flush">
    <?php if(empty($reviews['data'])): ?>
    <div class="acc-empty"><div class="ae-icon">⭐</div><p>You haven't reviewed any products yet.</p><a href="<?= APP_URL ?>/products" class="ae-btn">Browse Products</a></div>
    <?php else: foreach($reviews['data'] as $rv):
      $img = !empty($rv['product_image']) ? '<img src="'.UPLOAD_URL.'/'.$rv['product_image'].'" alt="'.e($rv['product_name'] ?? 'Product image').'">' : '<span>🛍️</span>';
    ?>
    <div class="rc-wrap">
      <div class="review-card bare">
        <div class="rc-img"><?= $img ?></div>
        <div>
          <div class="rc-product">📦 <?= e($rv['product_name']) ?></div>
          <div class="rc-stars"><?= str_repeat('★',$rv['rating']).str_repeat('☆',5-$rv['rating']) ?></div>
          <?php if($rv['title']): ?><div class="rc-title"><?= e($rv['title']) ?></div><?php endif; ?>
          <div class="rc-body"><?= e($rv['body']) ?></div>
          <div class="rc-date"><?= formatDate($rv['created_at']) ?> · <?= $rv['is_approved']?'<span class="txt-green">Approved</span>':'<span class="txt-gold">Pending Approval</span>' ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
    </div></div></div>
