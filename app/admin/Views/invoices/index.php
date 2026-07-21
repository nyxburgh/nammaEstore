<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">GST Invoices</h5><small class="text-muted"><?= number_format($invoices['total']) ?> total</small></div>
  <a href="<?= $p ?>/invoices/export?date_from=<?= $filters['date_from'] ?>&date_to=<?= $filters['date_to'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export CSV</a>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end" onsubmit="return validateForm(this)">
  <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Invoice or order #..." value="<?= e($filters['search'] ?? '') ?>"></div>
  <div class="col-md-2"><label class="form-label" style="font-size:11px;">From</label><input type="date" name="date_from" id="invDateFrom" class="form-control" value="<?= e($filters['date_from']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-2"><label class="form-label" style="font-size:11px;">To</label><input type="date" name="date_to" class="form-control" data-gte-field="#invDateFrom" value="<?= e($filters['date_to']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form></div>

<div class="row g-2 mb-3">
  <?php foreach($summary as $s): ?>
  <div class="col-md-3"><div class="card"><div class="card-body">
    <div class="text-muted" style="font-size:11.5px;">GST @ <?= $s['gst_rate'] ?>% (<?= $s['invoice_count'] ?> invoices)</div>
    <div style="font-weight:700;">CGST <?= currency($s['cgst_amount']) ?> · SGST <?= currency($s['sgst_amount']) ?></div>
    <div style="font-size:12px;color:var(--muted);">IGST <?= currency($s['igst_amount']) ?> · Taxable <?= currency($s['taxable_amount']) ?></div>
  </div></div></div>
  <?php endforeach; ?>
  <?php if(empty($summary)): ?>
  <div class="col-12"><div class="card"><div class="card-body text-muted text-center">No GST activity in this period.</div></div></div>
  <?php endif; ?>
</div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('Invoice #','invoice_number',$p.'/invoices') ?></th><th>Order</th><th>Seller</th><th>Place of Supply</th><th>Tax Type</th><th>Taxable</th><th>Tax</th><th><?= sortHeader('Total','grand_total',$p.'/invoices') ?></th><th><?= sortHeader('Date','invoice_date',$p.'/invoices') ?></th><th></th></tr></thead>
  <tbody>
  <?php if(empty($invoices['data'])): ?>
    <tr><td colspan="10"><div class="empty-state"><i class="bi bi-receipt"></i><h6>No invoices in this period</h6></div></td></tr>
  <?php else: foreach($invoices['data'] as $inv): ?>
  <tr>
    <td><code><?= e($inv['invoice_number']) ?></code></td>
    <td><?= e($inv['order_number']) ?></td>
    <td><?= e($inv['shop_name'] ?? $inv['seller_name']) ?></td>
    <td><?= e($inv['place_of_supply']) ?></td>
    <td><?= $inv['is_interstate'] ? 'IGST' : 'CGST+SGST' ?></td>
    <td><?= currency($inv['taxable_amount']) ?></td>
    <td><?= currency($inv['total_tax']) ?></td>
    <td style="font-weight:700;"><?= currency($inv['grand_total']) ?></td>
    <td style="font-size:11.5px;"><?= formatDate($inv['invoice_date']) ?></td>
    <td><a href="<?= $p ?>/invoices/<?= $inv['id'] ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($invoices['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($invoices,$p.'/invoices') ?></div><?php endif; ?>
</div>
