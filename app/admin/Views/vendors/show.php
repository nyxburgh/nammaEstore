<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= $p ?>/vendors" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-fill"><h5 style="font-weight:800;margin:0;"><?= e($vendor['shop_name']??$vendor['name']) ?></h5><small class="text-muted"><?= e($vendor['email']) ?> &bull; ID #<?= $vendor['id'] ?></small></div>
  <?php if(\App\Core\Auth::isSuperAdmin()): ?>
  <div class="d-flex gap-2">
    <a href="<?= $p ?>/vendors/<?= $vendor['id'] ?>/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php if(($vendor['vendor_status']??'')==='pending'): ?>
      <form method="post" action="<?= $p ?>/vendors/<?= $vendor['id'] ?>/approve">
  <?= csrf_field() ?><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button></form>
      <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg me-1"></i>Reject</button>
    <?php elseif(($vendor['vendor_status']??'')==='active'): ?>
      <form method="post" action="<?= $p ?>/vendors/<?= $vendor['id'] ?>/suspend" data-confirm="Suspend this vendor?">
  <?= csrf_field() ?><button class="btn btn-warning">Suspend</button></form>
    <?php elseif(($vendor['vendor_status']??'')==='suspended'): ?>
      <form method="post" action="<?= $p ?>/vendors/<?= $vendor['id'] ?>/approve">
  <?= csrf_field() ?><button class="btn btn-success">Reactivate</button></form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card mb-3"><div class="card-body text-center" style="padding:26px;">
      <div style="width:62px;height:62px;border-radius:18px;background:var(--grad);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;color:#fff;"><?= strtoupper(substr($vendor['shop_name']??$vendor['name'],0,1)) ?></div>
      <h6 style="font-weight:800;margin-bottom:4px;"><?= e($vendor['shop_name']??'—') ?></h6>
      <p style="color:var(--muted);font-size:13px;margin-bottom:10px;"><?= e($vendor['name']) ?></p>
      <?= statusBadge($vendor['vendor_status']??'pending') ?>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);text-align:left;">
        <?php foreach([['Email',$vendor['email']],['Phone',$vendor['phone']??'—'],['Joined',formatDate($vendor['created_at'])],['GST',$vendor['gst_number']??'—']] as [$l,$v]): ?>
        <div class="d-flex justify-content-between py-1"><span style="font-size:12px;color:var(--muted);"><?= $l ?></span><span style="font-size:12px;font-weight:600;"><?= e($v) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div></div>
    <div class="card"><div class="card-header"><span class="card-title">Subscription</span></div><div class="card-body">
      <div style="background:var(--purple-soft);border-radius:10px;padding:12px;text-align:center;"><div style="font-size:10px;color:var(--purple);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Plan</div><div style="font-size:18px;font-weight:800;color:var(--purple);"><?= e($vendor['plan_name']??'Free Plan') ?></div></div>
      <div class="d-flex justify-content-between pt-3"><span style="font-size:12.5px;color:var(--muted);">Monthly Turnover</span><span style="font-size:13px;font-weight:700;color:var(--purple);"><?= currency((float)($vendor['monthly_turnover']??0)) ?></span></div>
    </div></div>
  </div>
  <div class="col-lg-8">
    <div class="row g-2 mb-3">
      <?php foreach([['Gross Sales',(float)($earnings['gross_sales']??0),'currency'],['Net Earning',(float)($earnings['total_earning']??0),'currency'],['Commission',(float)($earnings['total_commission']??0),'currency'],['Orders',(int)($earnings['total_orders']??0),'number']] as [$l,$v,$t]): ?>
      <div class="col-md-3"><div class="stat-card" style="padding:14px;"><div><div class="stat-label"><?= $l ?></div><div class="stat-val" style="font-size:18px;"><?= $t==='currency'?currency($v):number_format($v) ?></div></div></div></div>
      <?php endforeach; ?>
    </div>
    <?php if((float)($earnings['pending_payout']??0)>0): ?><div class="alert alert-warning mb-3"><i class="bi bi-clock-fill me-2"></i>Pending payout: <strong><?= currency((float)$earnings['pending_payout']) ?></strong></div><?php endif; ?>
    <div class="card"><div class="card-header"><span class="card-title">Shop Info</span></div><div class="card-body"><div class="row g-3">
      <?php foreach([['Shop Name',$vendor['shop_name']??'—'],['URL Slug',$vendor['shop_slug']??'—'],['PAN',$vendor['pan_number']??'—'],['Bank (last 4)',$vendor['bank_account']?'****'.substr($vendor['bank_account'],-4):'—']] as [$l,$v]): ?>
      <div class="col-md-6"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;"><?= $l ?></div><div style="font-size:14px;font-weight:600;"><?= e($v) ?></div></div>
      <?php endforeach; ?>
      <?php if(!empty($vendor['description'])): ?><div class="col-12"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Description</div><div style="font-size:13.5px;color:var(--muted);"><?= e($vendor['description']) ?></div></div><?php endif; ?>
    </div>
    <?php if(!empty($vendor['rejection_note'])): ?><div class="alert alert-danger mt-3"><strong>Rejection Note:</strong> <?= e($vendor['rejection_note']) ?></div><?php endif; ?>
    </div></div>
  </div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:16px;">
  <div class="modal-header"><h6 class="modal-title" style="font-weight:700;">Reject Vendor</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="POST" action="<?= $p ?>/vendors/<?= $vendor['id'] ?>/reject">
  <?= csrf_field() ?>
    <div class="modal-body"><label class="form-label">Reason *</label><textarea name="rejection_note" class="form-control" rows="4" required placeholder="Explain why..."></textarea></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
  </form>
</div></div></div>
