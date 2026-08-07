<?php
// Section router — each section renders into $content inline
$sec = $section ?? 'overview';
?>

<?php if($sec === 'overview'): ?>
<!-- ══════════════════════════════════════
     OVERVIEW
══════════════════════════════════════ -->
<div class="page-hd">
  <div><h1>📊 Overview</h1><p>Welcome back, <?= e($seller['name']) ?>! Here's your store summary.</p></div>
  <div class="hd-actions">
    <a href="<?= SELLER_URL ?>/products/add" class="btn-pink btn-sm">➕ Add Product</a>
  </div>
</div>

<!-- Subscription Banner -->
<?php if(!empty($subInfo)): $si=$subInfo; $plan=$si['plan']; ?>
<div class="sub-banner">
  <div class="sub-info">
    <span class="sub-plan-badge">⭐ <?= e($plan['plan_name']??'Free Plan') ?></span>
    <div class="sub-text">
      <h4><?= $si['threshold']>0 ? 'Zero commission till '.currency($si['threshold']).' monthly turnover' : '10% commission on all sales' ?></h4>
      <p>This month: <?= currency($si['turnover']) ?><?= $si['threshold']>0?' of '.currency($si['threshold']).' used':' total turnover' ?></p>
    </div>
  </div>
  <?php if($si['threshold']>0): ?>
  <div class="sub-progress">
    <div class="sub-progress-label"><span>Turnover used</span><span><?= currency($si['turnover']) ?> / <?= currency($si['threshold']) ?></span></div>
    <div class="sub-bar"><div class="sub-fill" style="width:<?= min(100,$si['used_pct']) ?>%"></div></div>
  </div>
  <?php endif; ?>
  <a href="<?= SELLER_URL ?>/subscription" class="btn-outline btn-sm" style="background:rgba(255,255,255,.1);color:white;border-color:rgba(255,255,255,.3);">Upgrade Plan</a>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card pink"><div class="stat-icon">💰</div><div class="stat-label">Total Revenue</div><div class="stat-value"><?= currency($stats['revenue']??0) ?></div><div class="stat-change up">↑ Gross sales</div><div class="stat-deco">💰</div></div>
  <div class="stat-card blue"><div class="stat-icon">💵</div><div class="stat-label">Net Earnings</div><div class="stat-value"><?= currency($stats['earning']??0) ?></div><div class="stat-change up">↑ After commission</div><div class="stat-deco">💵</div></div>
  <div class="stat-card green"><div class="stat-icon">📦</div><div class="stat-label">Total Orders</div><div class="stat-value"><?= number_format($stats['orders']??0) ?></div><div class="stat-change <?= ($stats['pending']??0)>0?'up':'' ?>">⏳ <?= $stats['pending']??0 ?> pending</div><div class="stat-deco">📦</div></div>
  <div class="stat-card gold"><div class="stat-icon">🛍️</div><div class="stat-label">Active Products</div><div class="stat-value"><?= number_format($stats['products']??0) ?></div><div class="stat-change up">⭐ Rating: <?= number_format($stats['rating']??0,1) ?></div><div class="stat-deco">🛍️</div></div>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px;">
  <div class="card">
    <div class="card-head">
      <span class="card-title">📈 Revenue — Last 7 Days</span>
    </div>
    <div class="card-body">
      <?php $maxRev = max(1, max(array_column($weeklyData,'revenue'))); ?>
      <div class="bar-chart" id="revenueChart">
        <?php foreach($weeklyData as $wd): $h = max(4, round(($wd['revenue']/$maxRev)*76)); ?>
        <div class="bar-col">
          <div class="bar" style="height:<?= $h ?>px;" title="<?= currency($wd['revenue']) ?>"></div>
          <span class="bar-label"><?= $wd['day'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="chart-legend">
        <div class="legend-item"><div class="legend-dot" style="background:var(--pink-main)"></div>Revenue</div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><span class="card-title">🧾 Orders — Last 7 Days</span></div>
    <div class="card-body">
      <?php $maxOrd = max(1, max(array_column($weeklyData,'orders'))); ?>
      <div class="bar-chart">
        <?php foreach($weeklyData as $wd): $h = max(4, round(($wd['orders']/$maxOrd)*76)); ?>
        <div class="bar-col">
          <div class="bar blue" style="height:<?= $h ?>px;" title="<?= $wd['orders'] ?> orders"></div>
          <span class="bar-label"><?= $wd['day'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="chart-legend"><div class="legend-item"><div class="legend-dot" style="background:var(--blue-main)"></div>Orders</div></div>
    </div>
  </div>
</div>

<!-- Recent Orders -->
<div class="card">
  <div class="card-head">
    <span class="card-title">📦 Recent Orders</span>
    <a href="<?= SELLER_URL ?>/orders" class="btn-outline btn-sm">View All</a>
  </div>
  <div class="card-body no-pad">
    <?php if(empty($recentOrders)): ?>
    <div class="empty-state"><div class="es-icon">📦</div><h3>No orders yet</h3><p>Once customers place orders for your products, they'll appear here.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Order ID</th><th>Date</th><th>Customer</th><th>Product</th><th>Qty</th><th>Amount</th><th>Commission</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach(array_slice($recentOrders,0,8) as $o): ?>
          <tr>
            <td><strong>#<?= e($o['order_number']) ?></strong></td>
            <td><?= formatDate($o['placed_at']) ?></td>
            <td><?= e($o['customer_name']) ?></td>
            <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($o['product_name']) ?></td>
            <td><?= $o['quantity'] ?></td>
            <td><strong><?= currency($o['item_sub']) ?></strong></td>
            <td><span style="color:var(--red);font-size:.72rem;"><?= $o['commission_pct']??0 ?>% = <?= currency($o['commission_amount']??0) ?></span></td>
            <td><span class="status <?= e($o['order_status']) ?>"><?= ucfirst($o['order_status']) ?></span></td>
            <td><a href="<?= SELLER_URL ?>/orders/<?= $o['id'] ?>" class="btn-outline btn-sm">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Top Products -->
<?php if(!empty($topProducts)): ?>
<div class="card">
  <div class="card-head">
    <span class="card-title">🏆 Top Products</span>
    <a href="<?= SELLER_URL ?>/products" class="btn-outline btn-sm">Manage All</a>
  </div>
  <div class="card-body no-pad">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Sold</th><th>Revenue</th><th>Stock</th></tr></thead>
        <tbody>
          <?php foreach($topProducts as $p):
            $imgHtml = !empty($p['image']) ? '<img src="'.UPLOAD_URL.'/'.$p['image'].'" alt="'.e($p['name']).'">' : '<span>🛍️</span>';
          ?>
          <tr>
            <td><div class="prod-thumb"><div class="prod-thumb-img"><?= $imgHtml ?></div><div><div class="prod-thumb-name"><?= e($p['name']) ?></div><div class="prod-thumb-sku"><?= e($p['sku']??'—') ?></div></div></div></td>
            <td><span style="background:var(--pink-pale);color:var(--pink-deep);padding:2px 8px;border-radius:50px;font-size:.68rem;font-weight:700;"><?= e($p['cat_name']??'—') ?></span></td>
            <td><?= currency($p['sale_price']?:$p['price']) ?></td>
            <td><strong><?= number_format($p['total_sold']) ?></strong></td>
            <td style="color:var(--green);font-weight:700;"><?= currency($p['total_revenue']) ?></td>
            <td style="color:<?= $p['stock']<=5?'var(--red)':'var(--green)' ?>;font-weight:700;"><?= $p['stock'] ?><?= $p['stock']<=5?' 🔴':'' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php elseif($sec === 'orders'): ?>
<!-- ══════════════════════════════════════
     ORDERS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>📦 Orders</h1><p>Manage and process your customer orders</p></div></div>
<div style="display:flex;gap:6px;margin-bottom:16px;overflow-x:auto;padding-bottom:4px;">
  <?php foreach([['all','All'],['placed','⏳ Placed'],['processing','⚙️ Processing'],['shipped','🚚 Shipped'],['delivered','✅ Delivered'],['cancelled','❌ Cancelled']] as [$s,$l]): ?>
  <a href="?status=<?= $s ?>" class="period-tab <?= ($filters['status']??'all')===$s?'active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<form method="GET" class="filter-bar">
  <div class="filter-search"><span>🔍</span><input type="text" name="q" placeholder="Search order ID, customer, product..." value="<?= e($filters['q']??'') ?>"></div>
  <input type="hidden" name="status" value="<?= e($filters['status']??'all') ?>">
  <button type="submit" class="btn-pink btn-sm">Search</button>
</form>
<div class="card">
  <div class="card-body no-pad">
    <?php if(empty($orders['data'])): ?>
    <div class="empty-state"><div class="es-icon">📦</div><h3>No orders found</h3><p>Try adjusting your filters.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Order ID</th><th>Date</th><th>Customer</th><th>Product</th><th>Qty</th><th>Amount</th><th>Earning</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($orders['data'] as $o): ?>
          <tr>
            <td><strong>#<?= e($o['order_number']) ?></strong></td>
            <td><?= formatDate($o['placed_at']) ?></td>
            <td><?= e($o['customer_name']) ?></td>
            <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($o['product_name']) ?></td>
            <td><?= $o['quantity'] ?></td>
            <td><?= currency($o['item_sub']) ?></td>
            <td style="color:var(--green);font-weight:700;"><?= currency($o['seller_earning']??$o['item_sub']) ?></td>
            <td><span class="status <?= e($o['order_status']) ?>"><?= ucfirst($o['order_status']) ?></span></td>
            <td style="display:flex;gap:4px;flex-wrap:wrap;">
              <a href="<?= SELLER_URL ?>/orders/<?= $o['id'] ?>" class="btn-outline btn-sm">View</a>
              <?php if(in_array($o['order_status'],['placed','processing'])): ?>
              <button class="btn-pink btn-sm" onclick="updateStatus(<?= $o['id'] ?>,'shipped')">Ship</button>
              <?php elseif($o['order_status']==='shipped'): ?>
              <button class="btn-blue btn-sm" onclick="updateStatus(<?= $o['id'] ?>,'delivered')">Deliver</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php if(!empty($orders['total_pages']) && $orders['total_pages']>1): $cp=$orders['current_page']; $tp=$orders['total_pages']; ?>
<div class="pg-bar"><span>Showing <?= $orders['from'] ?>–<?= $orders['to'] ?> of <?= $orders['total'] ?></span><div class="pg-btns">
  <a href="?status=<?= $filters['status']??'all' ?>&page=<?= max(1,$cp-1) ?>" class="btn-outline btn-sm <?= $cp<=1?'disabled':'' ?>">‹ Prev</a>
  <a href="?status=<?= $filters['status']??'all' ?>&page=<?= min($tp,$cp+1) ?>" class="btn-outline btn-sm <?= $cp>=$tp?'disabled':'' ?>">Next ›</a>
</div></div>
<?php endif; ?>

<?php elseif($sec === 'order-detail' && !empty($order)): ?>
<!-- ══════════════════════════════════════
     ORDER DETAIL
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>📦 Order #<?= e($order['order_number']) ?></h1><p><?= formatDateTime($order['placed_at']) ?></p></div><div class="hd-actions"><a href="<?= SELLER_URL ?>/orders" class="btn-outline btn-sm">← Back to Orders</a></div></div>
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">
  <div>
    <div class="card"><div class="card-head"><span class="card-title">🛍️ Order Items</span><span class="status <?= e($order['order_status']) ?>"><?= ucfirst($order['order_status']) ?></span></div>
      <div class="card-body no-pad">
        <?php foreach($order['items'] as $item):
          $img = !empty($item['image']) ? '<img src="'.UPLOAD_URL.'/'.$item['image'].'" alt="'.e($item['product_name']).'">' : '<span>🛍️</span>';
        ?>
        <div style="display:flex;gap:12px;padding:14px;border-bottom:1px solid #fdf0f7;align-items:center;">
          <div class="prod-thumb-img" style="width:52px;height:52px;border-radius:10px;"><?= $img ?></div>
          <div style="flex:1;"><div style="font-size:.85rem;font-weight:700;"><?= e($item['product_name']) ?></div><div style="font-size:.72rem;color:var(--gray);">Qty: <?= $item['quantity'] ?> × <?= currency($item['unit_price']) ?></div></div>
          <div style="font-weight:700;color:var(--pink-deep);"><?= currency($item['unit_price']*$item['quantity']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if(!empty($order['split'])): $sp=$order['split']; ?>
        <div style="padding:14px;background:var(--green-pale);display:flex;justify-content:space-between;font-size:.82rem;">
          <span>💰 Commission: <?= $sp['commission_pct'] ?>% = <strong><?= currency($sp['commission_amount']) ?></strong></span>
          <span>Your Earning: <strong style="color:var(--green);"><?= currency($sp['seller_earning']) ?></strong></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!-- Update Status -->
    <?php if(!in_array($order['order_status'],['delivered','cancelled'])): ?>
    <div class="card"><div class="card-head"><span class="card-title">⚙️ Update Order Status</span></div>
      <div class="card-body">
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
          <?php foreach(['processing','shipped','delivered','cancelled'] as $s): if($s===$order['order_status']) continue; ?>
          <button class="btn-<?= $s==='delivered'?'blue':($s==='cancelled'?'danger':'pink') ?> btn-sm" onclick="updateStatus(<?= $order['id'] ?>,'<?= $s ?>')">
            <?= $s==='processing'?'⚙️ Mark Processing':($s==='shipped'?'🚚 Mark Shipped':($s==='delivered'?'✅ Mark Delivered':'❌ Cancel')) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <div class="form-group"><label class="form-label">Add a note (optional)</label><input type="text" class="form-input" id="statusNote" placeholder="e.g. Dispatched via DTDC"></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Shipment -->
    <div class="card"><div class="card-head"><span class="card-title">🚚 Shipment</span></div>
      <div class="card-body">
        <?php if(!empty($order['shipment'])): $sh=$order['shipment']; ?>
        <div style="font-size:.85rem;line-height:1.8;">
          <strong><?= e($sh['courier_name']) ?></strong> — Tracking #: <code><?= e($sh['tracking_number']) ?></code><br>
          Status: <span class="status <?= e($sh['status']) ?>"><?= ucwords(str_replace('_',' ',$sh['status'])) ?></span>
        </div>
        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
          <?php foreach(['picked_up','in_transit','out_for_delivery','delivered','failed','rto'] as $s): if($s===$sh['status']) continue; ?>
          <button class="btn-outline btn-sm" onclick="updateShipmentStatus(<?= $sh['id'] ?>,'<?= $s ?>')"><?= ucwords(str_replace('_',' ',$s)) ?></button>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <form onsubmit="createShipment(event, <?= $order['id'] ?>, <?= $order['split']['id'] ?? 0 ?>)">
          <div class="form-grid">
            <div class="form-group"><label class="form-label">Courier Name</label><input class="form-input" type="text" name="courier_name" placeholder="e.g. Delhivery, India Post" required></div>
            <div class="form-group"><label class="form-label">Tracking Number</label><input class="form-input" type="text" name="tracking_number" placeholder="Tracking #" required></div>
            <div class="form-group"><label class="form-label">Tracking URL (optional)</label><input class="form-input" type="text" name="tracking_url" placeholder="https://..."></div>
            <div class="form-group"><label class="form-label">Weight (kg)</label><input class="form-input" type="number" step="0.01" name="weight_kg" placeholder="0.500"></div>
          </div>
          <button type="submit" class="btn-pink btn-sm" style="margin-top:10px;">Create Shipment</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div>
    <div class="card"><div class="card-head"><span class="card-title">📍 Delivery Info</span></div>
      <div class="card-body" style="font-size:.83rem;line-height:1.8;">
        <strong><?= e($order['shipping_name']) ?></strong><br>
        <?= e($order['shipping_address']) ?><br>
        <?= e($order['shipping_city'].', '.$order['shipping_state'].' - '.$order['shipping_pincode']) ?><br>
        📞 <?= e($order['shipping_phone']) ?><br>
        ✉️ <?= e($order['customer_email']) ?><br>
        💳 <?= ucfirst($order['payment_method']) ?> · <?= ucfirst($order['payment_status']) ?>
      </div>
    </div>
    <?php if(!empty($order['timeline'])): ?>
    <div class="card"><div class="card-head"><span class="card-title">📍 Timeline</span></div>
      <div class="card-body" style="padding-top:8px;">
        <?php foreach($order['timeline'] as $tl): ?>
        <div style="display:flex;gap:10px;margin-bottom:12px;font-size:.8rem;">
          <div style="width:10px;height:10px;border-radius:50%;background:var(--pink-main);flex-shrink:0;margin-top:3px;"></div>
          <div><div style="font-weight:700;"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></div><?php if($tl['note']): ?><div style="color:var(--gray);"><?= e($tl['note']) ?></div><?php endif; ?><div style="font-size:.7rem;color:var(--gray);"><?= formatDateTime($tl['created_at']) ?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php elseif($sec === 'products'): ?>
<!-- ══════════════════════════════════════
     PRODUCTS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>🛍️ My Products</h1><p>Manage your product catalogue</p></div><div class="hd-actions"><a href="<?= SELLER_URL ?>/products/add" class="btn-pink btn-sm">➕ Add Product</a></div></div>
<form method="GET" class="filter-bar">
  <div class="filter-search"><span>🔍</span><input type="text" name="q" placeholder="Search by name or SKU..." value="<?= e($filters['q']??'') ?>"></div>
  <select name="category" class="filter-select" onchange="this.form.submit()"><option value="">All Categories</option><?php foreach($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($filters['category']??'')==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select>
  <select name="status" class="filter-select" onchange="this.form.submit()"><option value="all">All Status</option><option value="active" <?= ($filters['status']??'')==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= ($filters['status']??'')==='inactive'?'selected':'' ?>>Inactive</option></select>
  <button type="submit" class="btn-pink btn-sm">Search</button>
</form>
<div class="card">
  <div class="card-body no-pad">
    <?php if(empty($products['data'])): ?>
    <div class="empty-state"><div class="es-icon">🛍️</div><h3>No products found</h3><p>Add your first product to start selling!</p><a href="<?= SELLER_URL ?>/products/add" class="btn-pink">➕ Add Product</a></div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Sold</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($products['data'] as $p):
            $imgHtml = !empty($p['image']) ? '<img src="'.UPLOAD_URL.'/'.$p['image'].'" alt="'.e($p['name']).'">' : '<span>🛍️</span>';
          ?>
          <tr>
            <td><div class="prod-thumb"><div class="prod-thumb-img"><?= $imgHtml ?></div><div><div class="prod-thumb-name"><?= e($p['name']) ?></div><div class="prod-thumb-sku">SKU: <?= e($p['sku']??'—') ?></div></div></div></td>
            <td><?= e($p['cat_name']??'—') ?></td>
            <td>
              <?= currency($p['sale_price']?:$p['price']) ?>
              <?php if($p['sale_price']): ?><br><small style="text-decoration:line-through;color:var(--gray);"><?= currency($p['price']) ?></small><?php endif; ?>
            </td>
            <td style="color:<?= $p['stock']<=5?'var(--red)':($p['stock']<=15?'var(--orange)':'var(--green)') ?>;font-weight:700;"><?= $p['stock'] ?></td>
            <td><?= number_format($p['sold']) ?></td>
            <td><span class="status <?= e($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
            <td style="display:flex;gap:4px;flex-wrap:wrap;">
              <a href="<?= SELLER_URL ?>/products/<?= $p['id'] ?>/edit" class="btn-outline btn-sm">✏️ Edit</a>
              <button class="btn-outline btn-sm" onclick="toggleProductStatus(<?= $p['id'] ?>)">
                <?= $p['status']==='active'?'⏸️ Hide':'▶️ Show' ?>
              </button>
              <button class="btn-danger btn-sm" onclick="confirmDelete('<?= SELLER_URL ?>/products/<?= $p['id'] ?>/delete','Delete this product?')">🗑️</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php if(!empty($products['total_pages']) && $products['total_pages']>1): $cp=$products['current_page']; $tp=$products['total_pages']; ?>
<div class="pg-bar"><span>Showing <?= $products['from'] ?>–<?= $products['to'] ?> of <?= $products['total'] ?></span><div class="pg-btns">
  <a href="?page=<?= max(1,$cp-1) ?>" class="btn-outline btn-sm <?= $cp<=1?'disabled':'' ?>">‹ Prev</a>
  <a href="?page=<?= min($tp,$cp+1) ?>" class="btn-outline btn-sm <?= $cp>=$tp?'disabled':'' ?>">Next ›</a>
</div></div>
<?php endif; ?>

<?php elseif($sec === 'add-product'): ?>
<!-- ══════════════════════════════════════
     ADD / EDIT PRODUCT
══════════════════════════════════════ -->
<?php $isEdit = !empty($product); $formAction = $isEdit ? SELLER_URL.'/products/'.$product['id'].'/edit' : SELLER_URL.'/products/add'; ?>
<div class="page-hd">
  <div><h1><?= $isEdit?'✏️ Edit Product':'➕ Add Product' ?></h1><p><?= $isEdit?'Update your product details':'List a new product on the marketplace' ?></p></div>
  <div class="hd-actions"><a href="<?= SELLER_URL ?>/products" class="btn-outline btn-sm">Cancel</a></div>
</div>
<form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">
    <div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><span class="card-title">📝 Basic Information</span></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="form-group full"><label class="form-label">Product Name <span class="req">*</span></label><input class="form-input" type="text" name="name" required placeholder="e.g. Samsung 55-inch 4K Smart TV" value="<?= e($product['name']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Category <span class="req">*</span></label>
              <select class="form-select" name="category_id" required>
                <option value="">Select category</option>
                <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($product['category_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="form-group"><label class="form-label">Brand</label><input class="form-input" type="text" name="brand" placeholder="e.g. Samsung, Nike, Local Brand" value="<?= e($product['brand']??'') ?>"></div>
            <div class="form-group"><label class="form-label">SKU / Model No.</label><input class="form-input" type="text" name="sku" placeholder="Product code" value="<?= e($product['sku']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Barcode</label><input class="form-input" type="text" name="barcode" placeholder="EAN/UPC barcode" value="<?= e($product['barcode']??'') ?>"></div>
            <div class="form-group full"><label class="form-label">Description <span class="req">*</span></label><textarea class="form-textarea form-input" name="description" required placeholder="Describe your product in detail..."><?= e($product['description']??'') ?></textarea></div>
          </div>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><span class="card-title">💰 Pricing & Stock</span></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="form-group"><label class="form-label">MRP / Price <span class="req">*</span></label><input class="form-input" type="number" name="price" step="0.01" min="0" required placeholder="₹0.00" value="<?= e($product['price']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Sale Price</label><input class="form-input" type="number" name="sale_price" step="0.01" min="0" placeholder="₹0.00 (optional)" value="<?= e($product['sale_price']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Stock Quantity <span class="req">*</span></label><input class="form-input" type="number" name="stock" min="0" required placeholder="0" value="<?= e($product['stock']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Weight (kg)</label><input class="form-input" type="number" name="weight" step="0.01" min="0" placeholder="0.500" value="<?= e($product['weight']??'') ?>"></div>
            <div class="form-group"><label class="form-label">HSN Code</label><input class="form-input" type="text" name="hsn_code" placeholder="e.g. 6109" value="<?= e($product['hsn_code']??'') ?>"></div>
            <div class="form-group"><label class="form-label">GST Rate (%) <span class="req">*</span></label>
              <select class="form-input" name="gst_rate" required>
                <?php foreach([0,5,12,18,28] as $rate): ?>
                <option value="<?= $rate ?>" <?= (float)($product['gst_rate']??18)===(float)$rate?'selected':'' ?>><?= $rate ?>%</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="gst-note">ℹ️ Enter <strong>GST-inclusive</strong> prices. The selected GST rate is shown to buyers ("Inclusive of X% GST") and broken out separately in their cart, order summary and GST invoice.</div>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><span class="card-title">🏷️ Variants (optional)</span><button type="button" class="btn-outline btn-sm" onclick="addVariant()">+ Add Variant</button></div>
        <div class="card-body">
          <div id="variantRows">
            <?php if(!empty($product['variants'])): foreach($product['variants'] as $i=>$vr): ?>
            <div class="variant-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
              <input class="form-input" type="text" name="variants[<?= $i ?>][name]" placeholder="Size / Color..." value="<?= e($vr['variant_name']) ?>">
              <input class="form-input" type="text" name="variants[<?= $i ?>][value]" placeholder="XL / Red..." value="<?= e($vr['variant_value']) ?>">
              <input class="form-input" type="number" name="variants[<?= $i ?>][price_modifier]" placeholder="±₹" step="0.01" value="<?= e($vr['price_modifier']) ?>">
              <input class="form-input" type="number" name="variants[<?= $i ?>][stock]" placeholder="Stock" min="0" value="<?= e($vr['stock']) ?>">
              <button type="button" onclick="this.closest('.variant-row').remove()" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:1.1rem;">✕</button>
            </div>
            <?php endforeach; endif; ?>
          </div>
          <p class="form-hint">Add size, color, or other variants. Price modifier = extra cost (e.g. +50 for XL)</p>
        </div>
      </div>
    </div>
    <div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><span class="card-title">🖼️ Product Images</span></div>
        <div class="card-body">
          <?php if(!empty($product['images'])): ?>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
            <?php foreach($product['images'] as $img): ?>
            <div style="width:64px;height:64px;border-radius:8px;overflow:hidden;border:1.5px solid #f0e6ef;"><img src="<?= UPLOAD_URL.'/'.$img['image_path'] ?>" style="width:100%;height:100%;object-fit:cover;" alt="<?= e($product['name'] ?? 'Product image') ?>"></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <label class="upload-zone">
            <div class="up-icon">📷</div>
            <h4><?= $isEdit?'Add More Images':'Upload Images'?></h4>
            <p>JPG, PNG, WebP — max 2MB each</p>
            <input type="file" name="images[]" multiple accept="image/*" style="display:none;" onchange="showImgPreview(this)">
          </label>
          <div id="imgPreview" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;"></div>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><span class="card-title">⚙️ Settings</span></div>
        <div class="card-body">
          <div class="form-group" style="margin-bottom:12px;">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="active" <?= ($product['status']??'active')==='active'?'selected':'' ?>>Active — Visible to customers</option>
              <option value="inactive" <?= ($product['status']??'')==='inactive'?'selected':'' ?>>Inactive — Hidden</option>
            </select>
          </div>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.82rem;"><input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured'])?'checked':'' ?> style="accent-color:var(--pink-main);"> Mark as Featured</label>
        </div>
      </div>
      <button type="submit" class="btn-pink" style="width:100%;padding:13px;font-size:.95rem;justify-content:center;"><?= $isEdit?'💾 Update Product':'🚀 Publish Product' ?></button>
    </div>
  </div>
</form>

<?php elseif($sec === 'earnings'): ?>
<!-- ══════════════════════════════════════
     EARNINGS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>💰 Earnings</h1><p>Your revenue and payout overview</p></div>
<div class="hd-actions earn-period-tabs">
  <?php foreach(['today'=>'Today','week'=>'7 Days','month'=>'This Month','year'=>'This Year'] as $k=>$l): ?>
  <button class="period-tab <?= $k==='month'?'active':'' ?>" onclick="loadEarnings('<?= $k ?>',this)"><?= $l ?></button>
  <?php endforeach; ?>
</div></div>
<div class="earn-summary" id="earnSummary">
  <div class="earn-box"><div class="e-label">Gross Revenue</div><div class="e-val pink" id="eGross">—</div><div class="e-sub">Total sales amount</div></div>
  <div class="earn-box"><div class="e-label">Commission Paid</div><div class="e-val" style="color:var(--red);" id="eComm">—</div><div class="e-sub">Platform fee</div></div>
  <div class="earn-box"><div class="e-label">Net Earning</div><div class="e-val green" id="eNet">—</div><div class="e-sub">Your actual payout</div></div>
</div>
<div class="card">
  <div class="card-head"><span class="card-title">📈 Revenue Chart</span></div>
  <div class="card-body">
    <?php $maxRev = max(1, max(array_column($weeklyData,'revenue'))); ?>
    <div class="bar-chart">
      <?php foreach($weeklyData as $wd): $h = max(4, round(($wd['revenue']/$maxRev)*76)); ?>
      <div class="bar-col">
        <div class="bar" style="height:<?= $h ?>px;" title="<?= currency($wd['revenue']) ?>"></div>
        <span class="bar-label"><?= $wd['day'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php elseif($sec === 'commission'): ?>
<!-- ══════════════════════════════════════
     COMMISSION LOG
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>📈 Commission Log</h1><p>Detailed commission calculations per order</p></div></div>
<div class="card">
  <div class="card-body no-pad">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Order</th><th>Product</th><th>Plan</th><th>Turnover Before</th><th>Item Amount</th><th>Comm %</th><th>Comm Amount</th><th>Earning</th></tr></thead>
        <tbody id="commBody">
          <?php if(empty($commLog['data'])): ?>
          <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--gray);">No commission records yet. Start selling to see this data.</td></tr>
          <?php else: foreach($commLog['data'] as $c): ?>
          <tr>
            <td><?= formatDate($c['calculated_at']??'') ?></td>
            <td>#<?= e($c['order_number']) ?></td>
            <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;"><?= e($c['product_name']??'—') ?></td>
            <td><span style="background:var(--blue-pale);color:var(--blue-main);padding:2px 8px;border-radius:50px;font-size:.68rem;font-weight:700;"><?= e($c['plan_name']) ?></span></td>
            <td><?= currency($c['turnover_before']??0) ?></td>
            <td><?= currency($c['item_amount']) ?></td>
            <td style="color:var(--red);"><?= $c['commission_pct'] ?>%</td>
            <td style="color:var(--red);font-weight:700;"><?= currency($c['commission_amount']) ?></td>
            <td style="color:var(--green);font-weight:700;"><?= currency($c['seller_earning']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php elseif($sec === 'subscription'): ?>
<!-- ══════════════════════════════════════
     SUBSCRIPTION
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>⭐ Subscription Plans</h1><p>Choose the right plan for your business</p></div></div>
<div class="plans-grid">
  <?php
  $allPlans = $allPlans ?? [];
  $currentPlanId = $subInfo['plan']['plan_id'] ?? null;
  foreach($allPlans as $i=>$pl):
    $isCurrent = $pl['id'] == $currentPlanId;
  ?>
  <div class="plan-card <?= $i===1?'featured':'' ?> <?= $isCurrent?'active-plan':'' ?>">
    <div class="plan-name"><?= e($pl['name']) ?></div>
    <div class="plan-price"><sup>₹</sup><?= (float)$pl['price']===0?'0':number_format($pl['price']) ?><small>/month</small></div>
    <ul class="plan-features">
      <li><?= (float)$pl['price']===0?'Free forever':'Monthly subscription' ?></li>
      <li>Free tier: <?= currency($pl['free_threshold']) ?> turnover</li>
      <li><?= $pl['commission_after_threshold'] ?>% commission after threshold</li>
      <li>Full seller dashboard access</li>
      <li>Unlimited product listings</li>
    </ul>
    <button class="btn-plan <?= $isCurrent?'current':($i===1?'gold':'pink') ?>" <?= $isCurrent?'disabled':'' ?> onclick="showToast('📞 Contact admin to change plan: admin@nammaestore.com')">
      <?= $isCurrent?'✓ Current Plan':($i===0?'Downgrade to Free':'Upgrade') ?>
    </button>
  </div>
  <?php endforeach; ?>
</div>
<div class="card" style="margin-top:8px;">
  <div class="card-head"><span class="card-title">📊 Your Current Month</span></div>
  <div class="card-body">
    <?php if(!empty($subInfo)): $si=$subInfo; ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
      <div style="text-align:center;background:var(--pink-pale);padding:14px;border-radius:10px;"><div style="font-size:.72rem;color:var(--gray);font-weight:700;text-transform:uppercase;margin-bottom:4px;">Plan</div><div style="font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:var(--pink-deep);"><?= e($si['plan']['plan_name']) ?></div></div>
      <div style="text-align:center;background:var(--green-pale);padding:14px;border-radius:10px;"><div style="font-size:.72rem;color:var(--gray);font-weight:700;text-transform:uppercase;margin-bottom:4px;">This Month Turnover</div><div style="font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:var(--green);"><?= currency($si['turnover']) ?></div></div>
      <div style="text-align:center;background:var(--blue-pale);padding:14px;border-radius:10px;"><div style="font-size:.72rem;color:var(--gray);font-weight:700;text-transform:uppercase;margin-bottom:4px;">Free Remaining</div><div style="font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:var(--blue-main);"><?= $si['threshold']>0?currency(max(0,$si['threshold']-$si['turnover'])):'Flat 10%' ?></div></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php elseif($sec === 'reviews'): ?>
<!-- ══════════════════════════════════════
     REVIEWS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>⭐ Customer Reviews</h1><p>Reviews for your products</p></div></div>
<div class="card">
  <div class="card-body no-pad">
    <?php if(empty($reviewsData['data'])): ?>
    <div class="empty-state"><div class="es-icon">⭐</div><h3>No reviews yet</h3><p>Encourage customers to review your products.</p></div>
    <?php else: foreach($reviewsData['data'] as $rv): ?>
    <div style="padding:16px;border-bottom:1px solid #fdf0f7;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:8px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:34px;height:34px;border-radius:50%;background:var(--pink-main);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:.85rem;"><?= strtoupper(substr($rv['user_name'],0,1)) ?></div>
          <div><div style="font-weight:700;font-size:.85rem;"><?= e($rv['user_name']) ?></div><div style="font-size:.7rem;color:var(--gray);"><?= formatDate($rv['created_at']) ?></div></div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="color:var(--gold);"><?= str_repeat('★',$rv['rating']).str_repeat('☆',5-$rv['rating']) ?></div>
          <span class="status <?= $rv['is_approved']?'active':'pending' ?>"><?= $rv['is_approved']?'Approved':'Pending' ?></span>
        </div>
      </div>
      <div style="font-size:.8rem;color:var(--gray);margin-bottom:4px;">📦 <?= e($rv['product_name']) ?></div>
      <?php if($rv['title']): ?><div style="font-size:.85rem;font-weight:600;margin-bottom:4px;"><?= e($rv['title']) ?></div><?php endif; ?>
      <div style="font-size:.82rem;color:var(--dark);line-height:1.6;"><?= e($rv['body']) ?></div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php elseif($sec === 'settings'): ?>
<!-- ══════════════════════════════════════
     SETTINGS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>⚙️ Shop Settings</h1><p>Manage your shop profile and account</p></div></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
  <div>
    <div class="card">
      <div class="card-head"><span class="card-title">🏪 Shop Profile</span></div>
      <div class="card-body">
        <form method="POST" action="<?= SELLER_URL ?>/settings">
  <?= csrf_field() ?>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">Shop Name *</label><input class="form-input" type="text" name="shop_name" required value="<?= e($sellerProfile['shop_name']??'') ?>"></div>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">Your Name</label><input class="form-input" type="text" name="name" value="<?= e($seller['name']) ?>"></div>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">Phone</label><input class="form-input" type="tel" name="phone" value="<?= e($seller['phone']??'') ?>"></div>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">Shop Phone</label><input class="form-input" type="tel" name="shop_phone" value="<?= e($sellerProfile['shop_phone']??'') ?>"></div>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">Description</label><textarea class="form-input form-textarea" name="description"><?= e($sellerProfile['description']??'') ?></textarea></div>
          <button type="submit" class="btn-pink" style="width:100%;padding:11px;justify-content:center;">Save Shop Profile</button>
        </form>
      </div>
    </div>
  </div>
  <div>
    <div class="card">
      <div class="card-head"><span class="card-title">📍 Business Address</span></div>
      <div class="card-body">
        <form method="POST" action="<?= SELLER_URL ?>/settings">
  <?= csrf_field() ?>
          <div class="form-group" style="margin-bottom:10px;"><label class="form-label">Address</label><input class="form-input" type="text" name="address" value="<?= e($sellerProfile['address']??'') ?>" placeholder="Shop/Warehouse address"></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
            <div class="form-group"><label class="form-label">City</label><input class="form-input" type="text" name="city" value="<?= e($sellerProfile['city']??'') ?>"></div>
            <div class="form-group"><label class="form-label">State</label><input class="form-input" type="text" name="state" value="<?= e($sellerProfile['state']??'') ?>"></div>
          </div>
          <div class="form-group" style="margin-bottom:10px;"><label class="form-label">Pincode</label><input class="form-input" type="text" name="pincode" value="<?= e($sellerProfile['pincode']??'') ?>" maxlength="6"></div>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">GST Number</label><input class="form-input" type="text" name="gst_number" value="<?= e($sellerProfile['gst_number']??'') ?>" placeholder="22AAAAA0000A1Z5"></div>
          <button type="submit" class="btn-pink" style="width:100%;padding:11px;justify-content:center;">Save Address</button>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-head"><span class="card-title">🔒 Change Password</span></div>
      <div class="card-body">
        <form method="POST" action="<?= SELLER_URL ?>/settings/password">
  <?= csrf_field() ?>
          <div class="form-group" style="margin-bottom:10px;"><label class="form-label">Current Password</label><input class="form-input" type="password" name="current_password" required placeholder="Current password"></div>
          <div class="form-group" style="margin-bottom:10px;"><label class="form-label">New Password</label><input class="form-input" type="password" name="new_password" required placeholder="Min 8 characters"></div>
          <div class="form-group" style="margin-bottom:12px;"><label class="form-label">Confirm Password</label><input class="form-input" type="password" name="confirm_password" required placeholder="Repeat"></div>
          <button type="submit" class="btn-pink" style="width:100%;padding:11px;justify-content:center;">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php elseif($sec === 'wallet'): $wd = $walletData ?? ['wallet'=>['balance'=>0,'total_earned'=>0,'total_withdrawn'=>0],'pipeline'=>['pipeline'=>0,'awaiting_credit'=>0,'on_hold'=>0],'min_withdrawal'=>1000]; ?>
<!-- ══════════════════════════════════════
     WALLET & PAYOUTS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>👛 Wallet & Payouts</h1><p>Your settled earnings and payout requests</p></div></div>

<div class="stats-grid">
  <div class="stat-card pink"><div class="stat-icon">👛</div><div class="stat-label">Available Balance</div><div class="stat-value"><?= currency($wd['wallet']['balance']) ?></div><div class="stat-change">Ready to withdraw</div><div class="stat-deco">👛</div></div>
  <div class="stat-card blue"><div class="stat-icon">📥</div><div class="stat-label">Total Earned</div><div class="stat-value"><?= currency($wd['wallet']['total_earned']) ?></div><div class="stat-change">All-time credited</div><div class="stat-deco">📥</div></div>
  <div class="stat-card green"><div class="stat-icon">📤</div><div class="stat-label">Total Withdrawn</div><div class="stat-value"><?= currency($wd['wallet']['total_withdrawn']) ?></div><div class="stat-change">All-time paid out</div><div class="stat-deco">📤</div></div>
  <div class="stat-card gold"><div class="stat-icon">⏳</div><div class="stat-label">In Settlement Pipeline</div><div class="stat-value"><?= currency($wd['pipeline']['pipeline'] + $wd['pipeline']['awaiting_credit']) ?></div><div class="stat-change">Not yet credited</div><div class="stat-deco">⏳</div></div>
</div>

<?php if(($wd['pipeline']['on_hold'] ?? 0) > 0): ?>
<div class="alert" style="background:#fff3cd;border:1px solid #ffe08a;color:#7a5c00;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
  ⚠️ <?= currency($wd['pipeline']['on_hold']) ?> is currently <strong>on hold</strong> — usually an open return, dispute, or (if you're on a subscription plan) a lapsed subscription. Check the Returns tab or renew your plan to release it.
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:16px;">
  <div class="card">
    <div class="card-head"><span class="card-title">📜 Wallet Transactions</span></div>
    <div class="card-body" style="padding:0;">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Balance After</th></tr></thead>
        <tbody>
        <?php foreach(($transactions['data'] ?? []) as $t): ?>
          <tr>
            <td><?= date('d M Y, h:i A', strtotime($t['created_at'])) ?></td>
            <td><span class="status <?= $t['type']==='credit'?'active':'inactive' ?>"><?= $t['type']==='credit'?'⬆ Credit':'⬇ Debit' ?></span></td>
            <td><?= e($t['description']) ?></td>
            <td style="color:<?= $t['type']==='credit'?'var(--green)':'var(--red)' ?>;font-weight:600;"><?= $t['type']==='credit'?'+':'-' ?><?= currency($t['amount']) ?></td>
            <td><?= currency($t['balance_after']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($transactions['data'])): ?>
          <tr><td colspan="5" style="text-align:center;padding:24px;color:#999;">No wallet activity yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><span class="card-title">💸 Request Withdrawal</span></div>
    <div class="card-body">
      <form method="POST" action="<?= SELLER_URL ?>/wallet/withdraw">
        <?= csrf_field() ?>
        <div class="form-group" style="margin-bottom:10px;">
          <label class="form-label">Amount (min <?= currency($wd['min_withdrawal']) ?>)</label>
          <input class="form-input" type="number" step="0.01" min="<?= $wd['min_withdrawal'] ?>" name="amount" required placeholder="₹0.00">
        </div>
        <div class="form-group" style="margin-bottom:10px;">
          <label class="form-label">Payout Method</label>
          <select class="form-input" name="method" required>
            <option value="upi">UPI</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="wallet">Platform Wallet</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label">UPI ID / Account Details</label>
          <input class="form-input" type="text" name="method_details" placeholder="yourname@upi or account number">
        </div>
        <button type="submit" class="btn-pink" style="width:100%;padding:11px;justify-content:center;">Request Withdrawal</button>
      </form>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-head"><span class="card-title">🧾 Withdrawal History</span></div>
  <div class="card-body" style="padding:0;">
    <table class="data-table">
      <thead><tr><th>Requested</th><th>Amount</th><th>Method</th><th>Status</th><th>Processed</th></tr></thead>
      <tbody>
      <?php foreach(($withdrawalHistory['data'] ?? []) as $w): ?>
        <tr>
          <td><?= date('d M Y', strtotime($w['requested_at'])) ?></td>
          <td><?= currency($w['amount']) ?></td>
          <td><?= ucwords(str_replace('_',' ',$w['method'])) ?></td>
          <td><span class="status <?= $w['status']==='paid'?'delivered':($w['status']==='rejected'?'cancelled':'pending') ?>"><?= ucfirst($w['status']) ?></span></td>
          <td><?= $w['processed_at'] ? date('d M Y', strtotime($w['processed_at'])) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($withdrawalHistory['data'])): ?>
        <tr><td colspan="5" style="text-align:center;padding:24px;color:#999;">No withdrawal requests yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif($sec === 'returns'): ?>
<!-- ══════════════════════════════════════
     RETURNS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>↩️ Returns & Replacements</h1><p>Requests on your orders — approval is handled by the platform</p></div></div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <table class="data-table">
      <thead><tr><th>Order</th><th>Product</th><th>Customer</th><th>Type</th><th>Reason</th><th>Status</th><th>Requested</th></tr></thead>
      <tbody>
      <?php foreach(($returnsData['data'] ?? []) as $r): ?>
        <tr>
          <td><?= e($r['order_number']) ?></td>
          <td><?= e($r['product_name']) ?></td>
          <td><?= e($r['customer_name']) ?></td>
          <td><?= ucfirst($r['type']) ?></td>
          <td><?= e($r['reason']) ?></td>
          <td><span class="status <?= ($r['status']==='refunded'||$r['status']==='completed')?'delivered':($r['status']==='rejected'?'cancelled':'pending') ?>"><?= ucfirst($r['status']) ?></span></td>
          <td><?= date('d M Y', strtotime($r['requested_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($returnsData['data'])): ?>
        <tr><td colspan="7" style="text-align:center;padding:24px;color:#999;">No return requests on your orders.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif($sec === 'invoices'): ?>
<!-- ══════════════════════════════════════
     GST INVOICES
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>🧾 GST Invoices</h1><p>Tax invoices generated for your orders</p></div></div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <table class="data-table">
      <thead><tr><th>Invoice #</th><th>Order</th><th>Date</th><th>Place of Supply</th><th>Tax Type</th><th>Grand Total</th><th></th></tr></thead>
      <tbody>
      <?php foreach(($invoicesData['data'] ?? []) as $inv): ?>
        <tr>
          <td><?= e($inv['invoice_number']) ?></td>
          <td><?= e($inv['order_number']) ?></td>
          <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
          <td><?= e($inv['place_of_supply']) ?></td>
          <td><?= $inv['is_interstate'] ? 'IGST' : 'CGST+SGST' ?></td>
          <td style="font-weight:700;"><?= currency($inv['grand_total']) ?></td>
          <td><a href="<?= SELLER_URL ?>/invoices/<?= $inv['id'] ?>/download" class="btn-pink" style="padding:5px 12px;font-size:.72rem;">⬇ Download</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($invoicesData['data'])): ?>
        <tr><td colspan="7" style="text-align:center;padding:24px;color:#999;">No invoices generated yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif($sec === 'notifications'): ?>
<!-- ══════════════════════════════════════
     NOTIFICATIONS
══════════════════════════════════════ -->
<div class="page-hd"><div><h1>🔔 Notifications</h1><p>Updates on your orders, wallet, and stock</p></div></div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php foreach(($notifications['data'] ?? []) as $n): ?>
    <div style="display:flex;gap:12px;padding:14px 16px;border-bottom:1px solid #f0e6ef;<?= $n['is_read']?'':'background:#fdf5fa;' ?>" onclick="markNotifRead(<?= $n['id'] ?>, '<?= e($n['link'] ?? '') ?>')">
      <div style="font-size:1.3rem;">🔔</div>
      <div style="flex:1;">
        <div style="font-weight:700;font-size:.85rem;"><?= e($n['title']) ?></div>
        <div style="font-size:.78rem;color:#888;"><?= e($n['message']) ?></div>
        <div style="font-size:.7rem;color:#aaa;margin-top:4px;"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($notifications['data'])): ?>
      <div style="text-align:center;padding:40px;color:#999;">No notifications yet.</div>
    <?php endif; ?>
  </div>
</div>

<?php endif; ?>

<?php $variantCount = count($product['variants'] ?? []); ?>
<?php $scripts = '<script>
let varIdx = ' . $variantCount . ';
function addVariant(){
  const row = `<div class="variant-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
    <input class="form-input" type="text" name="variants[${varIdx}][name]" placeholder="Size / Color...">
    <input class="form-input" type="text" name="variants[${varIdx}][value]" placeholder="XL / Red...">
    <input class="form-input" type="number" name="variants[${varIdx}][price_modifier]" placeholder="±₹" step="0.01">
    <input class="form-input" type="number" name="variants[${varIdx}][stock]" placeholder="Stock" min="0">
    <button type="button" onclick="this.closest(\'.variant-row\').remove()" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:1.1rem;">✕</button>
  </div>`;
  document.getElementById("variantRows").insertAdjacentHTML("beforeend",row);
  varIdx++;
}
function showImgPreview(input){
  const prev=document.getElementById("imgPreview");
  prev.innerHTML="";
  for(const f of input.files){
    const url=URL.createObjectURL(f);
    prev.insertAdjacentHTML("beforeend",`<div style="width:60px;height:60px;border-radius:8px;overflow:hidden;border:1.5px solid #f0e6ef;"><img src="${url}" alt="Selected product image preview" style="width:100%;height:100%;object-fit:cover;"></div>`);
  }
}
function loadEarnings(period, btn){
  document.querySelectorAll(".period-tab").forEach(b=>b.classList.remove("active"));
  btn.classList.add("active");
  fetch(SELLER_URL+"/earnings?period="+period)
    .then(r=>r.json()).then(d=>{
      const s=d.summary;
      document.getElementById("eGross").textContent="₹"+parseFloat(s.gross).toLocaleString("en-IN",{minimumFractionDigits:2});
      document.getElementById("eComm").textContent="₹"+parseFloat(s.comm).toLocaleString("en-IN",{minimumFractionDigits:2});
      document.getElementById("eNet").textContent="₹"+parseFloat(s.net).toLocaleString("en-IN",{minimumFractionDigits:2});
    });
}
function updateStatus(orderId, status){
  const note = document.getElementById("statusNote")?.value || "";
  const fd=new FormData(); fd.append("status",status); fd.append("note",note); fd.append("_csrf_token",CSRF_TOKEN);
  fetch(SELLER_URL+"/orders/"+orderId+"/status",{method:"POST",body:fd})
    .then(r=>r.json()).then(d=>{
      if(d.success){showToast("✅ Status updated to "+status);setTimeout(()=>location.reload(),800);}
      else showToast("⚠️ "+d.message);
    });
}
function createShipment(e, orderId, splitId){
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append("_csrf_token", CSRF_TOKEN);
  fd.append("order_id", orderId);
  fd.append("split_id", splitId);
  fetch(SELLER_URL+"/shipments/create",{method:"POST",body:fd})
    .then(r=>r.json()).then(d=>{
      if(d.success){showToast("✅ Shipment created");setTimeout(()=>location.reload(),800);}
      else showToast("⚠️ "+d.message);
    });
}
function updateShipmentStatus(shipmentId, status){
  const fd=new FormData(); fd.append("status",status); fd.append("_csrf_token",CSRF_TOKEN);
  fetch(SELLER_URL+"/shipments/"+shipmentId+"/track",{method:"POST",body:fd})
    .then(r=>r.json()).then(d=>{
      if(d.success){showToast("✅ Marked "+status);setTimeout(()=>location.reload(),800);}
      else showToast("⚠️ "+d.message);
    });
}
function markNotifRead(id, link){
  const fd=new FormData(); fd.append("_csrf_token",CSRF_TOKEN);
  fetch(SELLER_URL+"/notifications/"+id+"/read",{method:"POST",body:fd}).then(()=>{ if(link) location.href=link; });
}
// Load earnings on page load if on earnings section
if(document.querySelector(".earn-summary")){loadEarnings("month",document.querySelector(".period-tab.active"));}
</script>'; ?>
