<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/customers" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><div class="flex-fill"><h5 style="font-weight:800;margin:0;"><?= e($customer['name']) ?></h5><small class="text-muted">Customer #<?= $customer['id'] ?></small></div>
<?php if(\App\Core\Auth::isSuperAdmin()): ?><a href="<?= $p ?>/customers/<?= $customer['id'] ?>/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
</div>
<div class="row g-3"><div class="col-md-4"><div class="card"><div class="card-body text-center" style="padding:26px;">
  <div style="width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#0ea5e9,#6d28d9);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;"><?= strtoupper(substr($customer['name'],0,1)) ?></div>
  <h6 style="font-weight:700;"><?= e($customer['name']) ?></h6><p style="color:var(--muted);font-size:13px;"><?= e($customer['email']) ?></p>
  <?= statusBadge($customer['is_active']?'active':'inactive') ?>
  <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);text-align:left;">
    <?php foreach([['Phone',$customer['phone']??'—'],['Verified',$customer['is_verified']?'Yes ✅':'No'],['Joined',formatDate($customer['created_at'])],['Last Login',$customer['last_login']?timeAgo($customer['last_login']):'Never']] as [$l,$v]): ?>
    <div class="d-flex justify-content-between py-1"><span style="font-size:12px;color:var(--muted);"><?= $l ?></span><span style="font-size:12px;font-weight:600;"><?= e($v) ?></span></div>
    <?php endforeach; ?>
  </div>
</div></div></div>
<div class="col-md-8"><div class="card"><div class="card-header"><span class="card-title">Order History</span></div><div class="card-body"><div class="empty-state" style="padding:30px;"><i class="bi bi-bag"></i><h6>Order history will appear here</h6></div></div></div></div></div>
