<?php
/**
 * Invoice HTML template.
 * Expects $invoice (with 'items') and $company (settings array) in scope.
 * Kept as plain inline-styled HTML/CSS — Dompdf only supports a
 * subset of CSS, and this needs to render identically whether it
 * goes through Dompdf or is saved as a plain .html fallback.
 */
$fmt = fn($n) => number_format((float) $n, 2);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice <?= e($invoice['invoice_number']) ?></title>
<style>
  body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #222; margin: 0; padding: 30px; }
  .header { display: table; width: 100%; margin-bottom: 20px; }
  .header .left, .header .right { display: table-cell; vertical-align: top; width: 50%; }
  .header .right { text-align: right; }
  h1 { font-size: 20px; margin: 0 0 4px 0; color: #c2185b; }
  .muted { color: #777; font-size: 11px; }
  .section-title { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #c2185b; margin-bottom: 4px; letter-spacing: .5px; }
  .addr-box { display: table; width: 100%; margin: 16px 0; }
  .addr-box .col { display: table-cell; width: 33%; vertical-align: top; padding-right: 12px; }
  table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
  table.items th { background: #fdf0f7; color: #c2185b; padding: 8px; text-align: left; font-size: 10.5px; text-transform: uppercase; }
  table.items td { padding: 8px; border-bottom: 1px solid #f0e6ef; font-size: 11.5px; }
  table.items tfoot td { border: none; font-weight: bold; }
  .totals { width: 260px; margin-left: auto; margin-top: 14px; }
  .totals table { width: 100%; }
  .totals td { padding: 4px 6px; font-size: 11.5px; }
  .totals .grand { font-size: 14px; font-weight: bold; color: #c2185b; border-top: 2px solid #c2185b; }
  .footer { margin-top: 30px; font-size: 10px; color: #999; text-align: center; }
</style>
</head>
<body>

<div class="header">
  <div class="left">
    <h1><?= e($company['company_name'] ?? 'Namma E Store') ?></h1>
    <div class="muted"><?= e($company['company_address'] ?? '') ?></div>
    <div class="muted">GSTIN: <?= e($company['company_gst_number'] ?? '—') ?></div>
  </div>
  <div class="right">
    <div class="section-title">Tax Invoice</div>
    <div><strong>Invoice #:</strong> <?= e($invoice['invoice_number']) ?></div>
    <div><strong>Date:</strong> <?= date('d M Y', strtotime($invoice['invoice_date'])) ?></div>
    <div><strong>Order #:</strong> <?= e($invoice['order_number']) ?></div>
  </div>
</div>

<div class="addr-box">
  <div class="col">
    <div class="section-title">Sold By</div>
    <div><strong><?= e($invoice['shop_name'] ?? $invoice['seller_name']) ?></strong></div>
    <div><?= e($invoice['seller_address'] ?? '') ?></div>
    <div><?= e($invoice['seller_city'] ?? '') ?><?= $invoice['seller_city'] ? ',' : '' ?> <?= e($invoice['seller_state'] ?? '') ?> <?= e($invoice['seller_pincode'] ?? '') ?></div>
    <div>GSTIN: <?= e($invoice['seller_gst'] ?? 'Unregistered') ?></div>
  </div>
  <div class="col">
    <div class="section-title">Billed To</div>
    <div><strong><?= e($invoice['customer_name']) ?></strong></div>
    <div><?= e($invoice['shipping_address']) ?></div>
    <div><?= e($invoice['shipping_city']) ?>, <?= e($invoice['shipping_state']) ?> <?= e($invoice['shipping_pincode']) ?></div>
  </div>
  <div class="col">
    <div class="section-title">Place of Supply</div>
    <div><?= e($invoice['place_of_supply']) ?></div>
    <div class="muted" style="margin-top:6px;"><?= $invoice['is_interstate'] ? 'Inter-state supply (IGST)' : 'Intra-state supply (CGST + SGST)' ?></div>
  </div>
</div>

<table class="items">
  <thead>
    <tr>
      <th>Item</th><th>HSN</th><th>Qty</th><th>Rate</th><th>Taxable</th><th>GST%</th>
      <?php if ($invoice['is_interstate']): ?><th>IGST</th><?php else: ?><th>CGST</th><th>SGST</th><?php endif; ?>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($invoice['items'] as $item): ?>
    <tr>
      <td><?= e($item['product_name']) ?></td>
      <td><?= e($item['hsn_code'] ?: '—') ?></td>
      <td><?= (int) $item['quantity'] ?></td>
      <td><?= $fmt($item['unit_price']) ?></td>
      <td><?= $fmt($item['taxable_amount']) ?></td>
      <td><?= $fmt($item['gst_rate']) ?>%</td>
      <?php if ($invoice['is_interstate']): ?>
      <td><?= $fmt($item['igst_amount']) ?></td>
      <?php else: ?>
      <td><?= $fmt($item['cgst_amount']) ?></td>
      <td><?= $fmt($item['sgst_amount']) ?></td>
      <?php endif; ?>
      <td><?= $fmt($item['line_total']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="totals">
  <table>
    <tr><td>Taxable Amount</td><td style="text-align:right;"><?= $fmt($invoice['taxable_amount']) ?></td></tr>
    <?php if ($invoice['is_interstate']): ?>
    <tr><td>IGST</td><td style="text-align:right;"><?= $fmt($invoice['igst_amount']) ?></td></tr>
    <?php else: ?>
    <tr><td>CGST</td><td style="text-align:right;"><?= $fmt($invoice['cgst_amount']) ?></td></tr>
    <tr><td>SGST</td><td style="text-align:right;"><?= $fmt($invoice['sgst_amount']) ?></td></tr>
    <?php endif; ?>
    <tr class="grand"><td>Grand Total</td><td style="text-align:right;">₹<?= $fmt($invoice['grand_total']) ?></td></tr>
  </table>
</div>

<div class="footer">
  This is a system-generated invoice and does not require a physical signature.<br>
  <?= e($company['company_name'] ?? 'Namma E Store') ?><?php if (!empty($company['company_pan'])): ?> · PAN: <?= e($company['company_pan']) ?><?php endif; ?>
</div>

</body>
</html>
