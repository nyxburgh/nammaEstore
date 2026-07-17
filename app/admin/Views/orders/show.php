<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= $p ?>/orders" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-fill"><h5 style="font-weight:800;margin:0;">Order #<?= e($order['order_number']) ?></h5><small class="text-muted"><?= formatDateTime($order['placed_at']) ?> &bull; <?= e($order['customer_name']) ?></small></div>
  <div class="d-flex gap-2"><?= statusBadge($order['payment_status']) ?><?= statusBadge($order['order_status']) ?></div>
</div>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3"><div class="card-header"><span class="card-title">Items (<?= count($order['items']) ?>)</span></div>
      <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Product</th><th>Vendor</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($order['items'] as $item): ?>
        <tr>
          <td><div class="d-flex align-items-center gap-2">
            <?php if($item['product_image']): ?><img src="<?= UPLOAD_URL.'/'.$item['product_image'] ?>" style="width:36px;height:36px;border-radius:7px;object-fit:cover;border:1px solid var(--border);"><?php else: ?><div style="width:36px;height:36px;background:var(--bg);border-radius:7px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--muted);"><i class="bi bi-image"></i></div><?php endif; ?>
            <div><div style="font-weight:600;font-size:13px;"><?= e($item['product_name']) ?></div><?php if($item['variant_label']): ?><div style="font-size:11px;color:var(--muted);"><?= e($item['variant_label']) ?></div><?php endif; ?></div>
          </div></td>
          <td style="font-size:13px;"><?= e($item['shop_name']??$item['vendor_name']) ?></td>
          <td style="font-weight:700;">×<?= $item['quantity'] ?></td>
          <td><?= currency($item['unit_price']) ?></td>
          <td style="font-weight:700;color:var(--purple);"><?= currency($item['subtotal']) ?></td>
          <td><?= statusBadge($item['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <div class="card-body" style="border-top:1px solid var(--border);"><div class="row justify-content-end"><div class="col-md-4">
        <?php foreach([['Subtotal',currency($order['subtotal'])],['Shipping',currency($order['shipping_charge'])]] as [$l,$v]): ?><div class="d-flex justify-content-between py-1"><span style="color:var(--muted);font-size:13px;"><?= $l ?></span><span style="font-weight:600;"><?= $v ?></span></div><?php endforeach; ?>
        <?php if($order['discount']>0): ?><div class="d-flex justify-content-between py-1"><span style="color:var(--muted);font-size:13px;">Discount</span><span style="font-weight:600;color:#dc2626;">-<?= currency($order['discount']) ?></span></div><?php endif; ?>
        <div class="d-flex justify-content-between py-2" style="border-top:2px solid var(--border);margin-top:4px;"><span style="font-weight:800;font-size:15px;">Total</span><span style="font-weight:800;font-size:15px;color:var(--purple);"><?= currency($order['total']) ?></span></div>
      </div></div></div>
    </div>
    <?php if(!empty($order['splits'])): ?>
    <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="bi bi-pie-chart me-1"></i>Commission Splits</span></div>
      <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Vendor</th><th>Gross</th><th>Commission %</th><th>Commission</th><th>Net Earning</th><th>Payout</th></tr></thead><tbody>
      <?php foreach($order['splits'] as $sp): ?><tr>
        <td style="font-weight:600;"><?= e($sp['shop_name']??$sp['vendor_name']) ?></td>
        <td style="font-weight:600;"><?= currency($sp['gross_amount']) ?></td>
        <td><span class="badge badge-warning"><?= number_format($sp['commission_pct'],1) ?>%</span></td>
        <td style="color:var(--pink);font-weight:700;"><?= currency($sp['commission_amount']) ?></td>
        <td style="color:#16a34a;font-weight:700;"><?= currency($sp['vendor_earning']) ?></td>
        <td><?= statusBadge($sp['payout_status']) ?></td>
      </tr><?php endforeach; ?>
      </tbody></table></div>
    </div>
    <?php endif; ?>
    <div class="card mb-3"><div class="card-body d-flex align-items-center justify-content-between">
      <div><i class="bi bi-receipt me-1"></i> GST Invoices for this order</div>
      <div>
        <a href="<?= ADMIN_URL ?>/invoices?search=<?= urlencode($order['order_number']) ?>" class="btn btn-sm btn-outline-secondary">View Invoices</a>
        <button class="btn btn-sm btn-primary" onclick="regenInvoices(<?= $order['id'] ?>)">Generate / Refresh</button>
      </div>
    </div></div>
    <div class="card"><div class="card-header"><span class="card-title"><i class="bi bi-shield-check me-1"></i>Admin Override — Update Status</span></div><div class="card-body">
      <form method="POST" action="<?= $p ?>/orders/<?= $order['id'] ?>/status" class="row g-3">
  <?= csrf_field() ?>
        <div class="col-md-4"><label class="form-label">New Status</label><select name="status" class="form-select"><?php foreach(['placed','confirmed','processing','shipped','delivered','cancelled','returned'] as $s): ?><option value="<?= $s ?>" <?= $order['order_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Note (optional)</label><input type="text" name="note" class="form-control" placeholder="Reason..."></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Update</button></div>
      </form>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-3"><div class="card-header"><span class="card-title">Customer</span></div><div class="card-body">
      <div class="d-flex align-items-center gap-2 mb-3"><div class="av av-md"><?= strtoupper(substr($order['customer_name'],0,1)) ?></div><div><div style="font-weight:700;"><?= e($order['customer_name']) ?></div><div style="font-size:12px;color:var(--muted);"><?= e($order['customer_email']) ?></div></div></div>
      <div style="font-size:13px;color:var(--muted);">Phone: <?= e($order['customer_phone']??'—') ?></div>
    </div></div>
    <div class="card mb-3"><div class="card-header"><span class="card-title">Shipping Address</span></div><div class="card-body" style="font-size:13.5px;line-height:1.9;"><strong><?= e($order['shipping_name']) ?></strong><br><?= e($order['shipping_phone']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> — <?= e($order['shipping_pincode']) ?></div></div>
    <?php if($order['payment']): ?>
    <div class="card mb-3"><div class="card-header"><span class="card-title">Payment</span></div><div class="card-body">
      <?php foreach([['Method',strtoupper($order['payment']['gateway']??$order['payment_method'])],['Status',statusBadge($order['payment']['status'])],['Amount',currency($order['payment']['amount'])]] as [$l,$v]): ?>
      <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border);"><span style="font-size:12.5px;color:var(--muted);"><?= $l ?></span><span style="font-size:13px;font-weight:600;"><?= $v ?></span></div>
      <?php endforeach; ?>
    </div></div>
    <?php endif; ?>
    <div class="card"><div class="card-header"><span class="card-title">Timeline</span></div><div class="card-body" style="padding:14px 18px;">
      <?php if(empty($order['timeline'])): ?><div style="color:var(--muted);font-size:13px;">No entries yet.</div>
      <?php else: ?><div style="position:relative;padding-left:18px;"><div style="position:absolute;left:5px;top:6px;bottom:6px;width:2px;background:var(--border);border-radius:2px;"></div>
        <?php foreach($order['timeline'] as $i=>$tl): ?><div style="position:relative;margin-bottom:14px;"><div style="position:absolute;left:-18px;top:3px;width:10px;height:10px;border-radius:50%;background:<?= $i===count($order['timeline'])-1?'var(--purple)':'var(--border)' ?>;"></div>
          <div style="font-size:13px;font-weight:700;text-transform:capitalize;"><?= e($tl['status']) ?></div>
          <?php if($tl['note']): ?><div style="font-size:12px;color:var(--muted);"><?= e($tl['note']) ?></div><?php endif; ?>
          <div style="font-size:11px;color:var(--muted);"><?= formatDateTime($tl['created_at']) ?> &bull; <?= ucfirst(str_replace('_',' ',$tl['changed_by_type'])) ?></div>
        </div><?php endforeach; ?>
      </div><?php endif; ?>
    </div></div>
  </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function regenInvoices(orderId){
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN);
  fetch('<?= ADMIN_URL ?>/invoices/order/'+orderId+'/regenerate', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) alert('Invoices generated.'); location.reload(); });
}
</script>
