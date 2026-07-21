<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Gift Vouchers</h5><small class="text-muted"><?= number_format($giftCards['total']) ?> total</small></div>
  <a href="<?= $p ?>/gift-cards/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Issue Voucher</a>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Voucher code..." value="<?= e($filters['search']??'') ?>"></div>
  <div class="col-md-3"><select name="type" class="form-select" onchange="this.form.submit()">
    <option value="">All Types</option>
    <?php foreach(['company','seller','recharge'] as $t): ?>
    <option value="<?= $t ?>" <?= ($filters['type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/gift-cards') ?></th><th><?= sortHeader('Code','code',$p.'/gift-cards') ?></th><th>Type</th><th>Seller</th><th>Initial</th><th><?= sortHeader('Balance','current_balance',$p.'/gift-cards') ?></th><th>Status</th><th>Expires</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($giftCards['data'])): ?>
    <tr><td colspan="9"><div class="empty-state"><i class="bi bi-gift"></i><h6>No gift vouchers issued yet</h6></div></td></tr>
  <?php else: foreach($giftCards['data'] as $gc): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;">#<?= $gc['id'] ?></td>
    <td><code style="font-weight:700;"><?= e($gc['code']) ?></code></td>
    <td><?= ucfirst($gc['type']) ?></td>
    <td><?= e($gc['seller_name'] ?? '—') ?></td>
    <td><?= currency($gc['initial_balance']) ?></td>
    <td style="font-weight:700;"><?= currency($gc['current_balance']) ?></td>
    <td><?= statusBadge($gc['status']) ?></td>
    <td style="font-size:11.5px;"><?= $gc['expires_at'] ? formatDate($gc['expires_at']) : 'Never' ?></td>
    <td><a href="<?= $p ?>/gift-cards/<?= $gc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($giftCards['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($giftCards,$p.'/gift-cards') ?></div><?php endif; ?>
</div>
