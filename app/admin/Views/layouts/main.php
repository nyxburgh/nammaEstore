<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($title??'Dashboard') ?> — Namma E Store Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{
  --pink:#e91e8c;--pink-soft:#fde8f4;
  --purple:#6d28d9;--purple-light:#8b5cf6;--purple-soft:#ede9fe;
  --grad:linear-gradient(135deg,#6d28d9,#e91e8c);
  --sb-w:256px;--sb-bg:#16042a;--sb-text:#c4b5d6;
  --bg:#f8f5ff;--card:#fff;--border:#ede9fe;
  --text:#1a0f2e;--muted:#9c8ab0;
  --shadow:0 2px 14px rgba(109,40,217,.08);
  --shadow-md:0 6px 30px rgba(109,40,217,.14);
  --radius:14px;--radius-sm:9px;
}
*{box-sizing:border-box;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;margin:0;}
/* SIDEBAR */
#sidebar{position:fixed;top:0;left:0;width:var(--sb-w);height:100vh;background:var(--sb-bg);display:flex;flex-direction:column;z-index:1000;overflow-y:auto;transition:transform .25s;}
#sidebar::-webkit-scrollbar{width:3px;}
#sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.08);}
.sb-brand{padding:18px 20px 14px;background:rgba(0,0,0,.2);border-bottom:1px solid rgba(255,255,255,.05);flex-shrink:0;}
.sb-brand a{display:flex;align-items:center;gap:10px;text-decoration:none;}
.sb-logo{width:38px;height:38px;border-radius:11px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0;}
.sb-name{font-size:17px;font-weight:800;color:#fff;}
.sb-tag{font-size:9px;font-weight:700;color:#ff5cb8;background:rgba(233,30,140,.15);border-radius:4px;padding:1px 7px;letter-spacing:.5px;text-transform:uppercase;margin-left:4px;}
.sb-user{padding:13px 18px;background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.05);display:flex;align-items:center;gap:10px;flex-shrink:0;}
.sb-av{width:34px;height:34px;border-radius:10px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;}
.sb-uname{font-size:13px;font-weight:600;color:#fff;}
.sb-urole{font-size:11px;color:#ff5cb8;text-transform:capitalize;}
.sb-nav{padding:10px 0;flex:1;}
.sb-sec{padding:10px 20px 3px;font-size:10px;font-weight:700;color:rgba(255,255,255,.2);letter-spacing:1.8px;text-transform:uppercase;}
.sb-nav a{display:flex;align-items:center;gap:10px;padding:9px 18px;margin:1px 8px;border-radius:9px;text-decoration:none;color:var(--sb-text);font-size:13.5px;font-weight:500;transition:all .16s;}
.sb-nav a i{font-size:15px;width:18px;flex-shrink:0;opacity:.75;}
.sb-nav a:hover{background:rgba(255,255,255,.07);color:#fff;}
.sb-nav a.active{background:linear-gradient(135deg,rgba(109,40,217,.45),rgba(233,30,140,.28));color:#fff;font-weight:600;}
.sb-nav a.active i{opacity:1;}
.sb-badge{margin-left:auto;background:var(--pink);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;}
.sb-footer{padding:14px 18px;border-top:1px solid rgba(255,255,255,.05);flex-shrink:0;}
.sb-footer a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.35);font-size:13px;text-decoration:none;transition:color .16s;}
.sb-footer a:hover{color:rgba(255,255,255,.75);}
/* MAIN */
#main{margin-left:var(--sb-w);min-height:100vh;display:flex;flex-direction:column;}
/* TOPBAR */
.topbar{position:sticky;top:0;z-index:900;background:#fff;border-bottom:1px solid var(--border);padding:0 26px;height:60px;display:flex;align-items:center;justify-content:space-between;}
.tb-left{display:flex;align-items:center;gap:12px;}
.tb-title{font-size:16px;font-weight:800;}
.tb-right{display:flex;align-items:center;gap:10px;}
.tb-btn{width:36px;height:36px;border-radius:9px;border:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:16px;transition:all .16s;position:relative;}
.tb-btn:hover{background:var(--purple-soft);color:var(--purple);}
.tb-btn .dot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--pink);border-radius:50%;border:2px solid #fff;}
.tb-admin{display:flex;align-items:center;gap:8px;padding:5px 10px 5px 5px;border-radius:9px;border:1px solid var(--border);cursor:pointer;transition:all .16s;}
.tb-admin:hover{background:var(--purple-soft);}
.tb-av{width:28px;height:28px;border-radius:8px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;}
.tb-aname{font-size:13px;font-weight:600;}
.tb-arole{font-size:11px;color:var(--muted);}
.breadcrumb{margin:0;padding:0;background:none;font-size:11.5px;}
.breadcrumb-item+.breadcrumb-item::before{color:var(--muted);}
.breadcrumb-item a{color:var(--muted);text-decoration:none;}
/* PAGE */
.page-body{padding:26px;flex:1;}
/* CARDS */
.card{border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);background:var(--card);}
.card-header{padding:13px 18px;border-bottom:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:space-between;}
.card-title{font-size:13.5px;font-weight:700;margin:0;}
.card-body{padding:18px;}
/* STAT CARDS */
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px;display:flex;align-items:center;gap:14px;box-shadow:var(--shadow);transition:transform .18s,box-shadow .18s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
.si{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.si.purple{background:var(--purple-soft);color:var(--purple);}
.si.pink{background:var(--pink-soft);color:var(--pink);}
.si.green{background:#dcfce7;color:#16a34a;}
.si.orange{background:#fff7ed;color:#ea580c;}
.si.blue{background:#eff6ff;color:#2563eb;}
.si.red{background:#fef2f2;color:#dc2626;}
.si.teal{background:#f0fdfa;color:#0d9488;}
.stat-label{font-size:11.5px;color:var(--muted);font-weight:500;margin-bottom:3px;}
.stat-val{font-size:22px;font-weight:800;line-height:1.1;}
.stat-sub{font-size:11px;color:var(--muted);margin-top:3px;}
.stat-sub .up{color:#16a34a;} .stat-sub .dn{color:#dc2626;}
/* TABLE */
.table{font-size:13px;margin:0;}
.table thead th{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);background:var(--bg);border-bottom:1px solid var(--border);padding:9px 14px;}
.sort-link{color:inherit;text-decoration:none;white-space:nowrap;}
.sort-link:hover{color:var(--purple,#6d28d9);}
.table tbody td{padding:10px 14px;vertical-align:middle;border-color:var(--border);}
.table tbody tr:hover{background:rgba(109,40,217,.025);}
/* BADGES */
.badge{font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;}
.badge-success{background:#dcfce7;color:#15803d;} .badge-warning{background:#fef9c3;color:#a16207;}
.badge-danger{background:#fee2e2;color:#b91c1c;} .badge-info{background:#e0f2fe;color:#0369a1;}
.badge-primary{background:var(--purple-soft);color:var(--purple);} .badge-secondary{background:#f3f4f6;color:#6b7280;}
/* BUTTONS */
.btn-primary{background:var(--grad);border:none;font-weight:600;border-radius:9px;padding:8px 18px;font-size:13.5px;}
.btn-primary:hover{background:linear-gradient(135deg,#5b21b6,#c9107a);color:#fff;}
.btn-outline-primary{border-color:var(--purple);color:var(--purple);font-weight:600;border-radius:9px;}
.btn-outline-primary:hover{background:var(--purple);color:#fff;}
.btn-sm{padding:5px 12px;font-size:12px;border-radius:7px;}
.btn-icon{width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;}
/* FORMS */
.form-control,.form-select{border:1px solid var(--border);border-radius:9px;padding:9px 13px;font-size:13.5px;font-family:'Plus Jakarta Sans',sans-serif;}
.form-control:focus,.form-select:focus{border-color:var(--purple-light);box-shadow:0 0 0 3px rgba(109,40,217,.1);outline:none;}
.form-label{font-size:13px;font-weight:600;margin-bottom:5px;}
.input-group-text{background:var(--bg);border-color:var(--border);font-size:13px;color:var(--muted);}
/* PAGINATION */
.pagination{gap:3px;margin:0;}
.page-link{border-radius:8px!important;border:1px solid var(--border);font-size:12.5px;color:var(--purple);padding:5px 11px;}
.page-link:hover{background:var(--purple-soft);color:var(--purple);}
.page-item.active .page-link{background:var(--grad);border-color:transparent;color:#fff;}
.page-item.disabled .page-link{color:var(--muted);}
/* ALERTS */
.alert{border-radius:9px;border:none;font-size:13.5px;padding:11px 15px;}
.alert-success{background:#dcfce7;color:#15803d;} .alert-danger{background:#fee2e2;color:#b91c1c;}
.alert-warning{background:#fef9c3;color:#a16207;} .alert-info{background:#e0f2fe;color:#0369a1;}
/* FLASH TOAST */
#flash-toast{position:fixed;top:18px;right:18px;z-index:9999;min-width:290px;max-width:400px;border-radius:11px;padding:13px 16px;font-size:13.5px;font-weight:600;box-shadow:var(--shadow-md);display:flex;align-items:center;gap:10px;animation:toastIn .3s ease;}
@keyframes toastIn{from{transform:translateX(110%);opacity:0;}to{transform:translateX(0);opacity:1;}}
@keyframes toastOut{from{opacity:1;}to{transform:translateX(110%);opacity:0;}}
/* AVATARS */
.av{display:inline-flex;align-items:center;justify-content:center;font-weight:700;color:#fff;background:var(--grad);border-radius:8px;flex-shrink:0;}
.av-sm{width:26px;height:26px;font-size:10px;border-radius:7px;}
.av-md{width:34px;height:34px;font-size:13px;border-radius:10px;}
/* MISC */
.empty-state{text-align:center;padding:50px 20px;}
.empty-state i{font-size:44px;color:var(--border);display:block;margin-bottom:12px;}
.empty-state h6{color:var(--muted);font-weight:600;font-size:14px;}
.filter-bar{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;}
.form-switch .form-check-input{width:34px;height:18px;cursor:pointer;}
.form-check-input:checked{background-color:var(--purple);border-color:var(--purple);}
@media(max-width:992px){#sidebar{transform:translateX(-100%);}#sidebar.show{transform:translateX(0);}#main{margin-left:0;}.page-body{padding:14px;}}
</style>
</head>
<body>

<?php $admin = \App\Core\Auth::admin(); ?>

<!-- SIDEBAR -->
<nav id="sidebar">
  <div class="sb-brand">
    <a href="<?= ADMIN_URL ?>/dashboard">
      <div class="sb-logo">NES</div>
      <div><span class="sb-name">Namma E Store</span><span class="sb-tag">Admin</span></div>
    </a>
  </div>
  <div class="sb-user">
    <div class="sb-av"><?= strtoupper(substr($admin['name']??'A',0,1)) ?></div>
    <div>
      <div class="sb-uname"><?= e($admin['name']) ?></div>
      <div class="sb-urole"><?= str_replace('_',' ',$admin['role']??'') ?></div>
    </div>
  </div>
  <div class="sb-nav">
    <div class="sb-sec">Main</div>
    <a href="<?= ADMIN_URL ?>/dashboard" class="<?= adminActive('dashboard') ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>

    <div class="sb-sec">Users</div>
    <a href="<?= ADMIN_URL ?>/vendors" class="<?= adminActive('vendors') ?>">
      <i class="bi bi-shop"></i> Vendors
      <?php if(!empty($stats['pending_vendors'] ?? $sidebarStats['pending_vendors'] ?? 0 ?? $sidebarStats['pending_vendors'] ?? null)): ?><span class="sb-badge"><?= $stats['pending_vendors'] ?? $sidebarStats['pending_vendors'] ?? 0 ?></span><?php endif; ?>
    </a>
    <a href="<?= ADMIN_URL ?>/customers" class="<?= adminActive('customers') ?>"><i class="bi bi-people-fill"></i> Customers</a>
    <?php if(\App\Core\Auth::isSuperAdmin()): ?>
    <a href="<?= ADMIN_URL ?>/sub-admins" class="<?= adminActive('sub-admins') ?>"><i class="bi bi-person-badge-fill"></i> Sub-Admins</a>
    <?php endif; ?>

    <?php if(\App\Core\Auth::can('products')): ?>
    <div class="sb-sec">Catalogue</div>
    <a href="<?= ADMIN_URL ?>/products" class="<?= adminActive('products') ?>"><i class="bi bi-box-seam-fill"></i> Products</a>
    <a href="<?= ADMIN_URL ?>/categories" class="<?= adminActive('categories') ?>"><i class="bi bi-tags-fill"></i> Categories</a>
    <a href="<?= ADMIN_URL ?>/reviews" class="<?= adminActive('reviews') ?>">
      <i class="bi bi-star-fill"></i> Reviews
      <?php if(!empty($stats['pending_reviews'] ?? $sidebarStats['pending_reviews'] ?? 0 ?? $sidebarStats['pending_reviews'] ?? null)): ?><span class="sb-badge"><?= $stats['pending_reviews'] ?? $sidebarStats['pending_reviews'] ?? 0 ?></span><?php endif; ?>
    </a>
    <?php endif; ?>

    <div class="sb-sec">Commerce</div>
    <a href="<?= ADMIN_URL ?>/orders" class="<?= adminActive('orders') ?>"><i class="bi bi-bag-check-fill"></i> Orders</a>
    <a href="<?= ADMIN_URL ?>/shipments" class="<?= adminActive('shipments') ?>"><i class="bi bi-truck"></i> Shipments</a>
    <a href="<?= ADMIN_URL ?>/returns" class="<?= adminActive('returns') ?>"><i class="bi bi-arrow-return-left"></i> Returns</a>
    <a href="<?= ADMIN_URL ?>/disputes" class="<?= adminActive('disputes') ?>"><i class="bi bi-exclamation-triangle-fill"></i> Disputes</a>
    <?php if(\App\Core\Auth::can('payments')): ?>
    <a href="<?= ADMIN_URL ?>/payments" class="<?= adminActive('payments') ?>"><i class="bi bi-credit-card-fill"></i> Payments</a>
    <a href="<?= ADMIN_URL ?>/settlements" class="<?= adminActive('settlements') ?>"><i class="bi bi-wallet2"></i> Settlements</a>
    <?php endif; ?>

    <?php if(\App\Core\Auth::can('reports')): ?>
    <div class="sb-sec">Analytics</div>
    <a href="<?= ADMIN_URL ?>/reports" class="<?= adminActive('reports') ?>"><i class="bi bi-bar-chart-fill"></i> Reports</a>
    <a href="<?= ADMIN_URL ?>/invoices" class="<?= adminActive('invoices') ?>"><i class="bi bi-receipt"></i> GST Invoices</a>
    <?php endif; ?>

    <?php if(\App\Core\Auth::can('coupons')): ?>
    <div class="sb-sec">Marketing</div>
    <a href="<?= ADMIN_URL ?>/coupons" class="<?= adminActive('coupons') ?>"><i class="bi bi-ticket-perforated"></i> Coupons</a>
    <a href="<?= ADMIN_URL ?>/gift-cards" class="<?= adminActive('gift-cards') ?>"><i class="bi bi-gift"></i> Gift Vouchers</a>
    <a href="<?= ADMIN_URL ?>/brands" class="<?= adminActive('brands') ?>"><i class="bi bi-award"></i> Brands</a>
    <?php endif; ?>

    <?php if(\App\Core\Auth::can('banners') || \App\Core\Auth::can('pages')): ?>
    <div class="sb-sec">Content</div>
    <?php endif; ?>
    <?php if(\App\Core\Auth::can('banners')): ?>
    <a href="<?= ADMIN_URL ?>/banners" class="<?= adminActive('banners') ?>"><i class="bi bi-images"></i> Banners</a>
    <?php endif; ?>
    <?php if(\App\Core\Auth::can('pages')): ?>
    <a href="<?= ADMIN_URL ?>/pages" class="<?= adminActive('pages') ?>"><i class="bi bi-file-earmark-text"></i> Info Pages</a>
    <?php endif; ?>

    <?php if(\App\Core\Auth::can('activity')): ?>
    <a href="<?= ADMIN_URL ?>/activity" class="<?= adminActive('activity') ?>"><i class="bi bi-clock-history"></i> Activity Logs</a>
    <?php endif; ?>

    <?php if(\App\Core\Auth::isSuperAdmin()): ?>
    <div class="sb-sec">System</div>
    <a href="<?= ADMIN_URL ?>/settings" class="<?= adminActive('settings') ?>"><i class="bi bi-gear-fill"></i> Settings</a>
    <?php endif; ?>
  </div>
  <div class="sb-footer">
    <a href="<?= ADMIN_URL ?>/logout"><i class="bi bi-box-arrow-left"></i> Sign Out</a>
  </div>
</nav>

<!-- MAIN -->
<div id="main">
  <div class="topbar">
    <div class="tb-left">
      <button class="tb-btn d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="bi bi-list"></i></button>
      <div>
        <div class="tb-title"><?= e($title??'Dashboard') ?></div>
        <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= ADMIN_URL ?>/dashboard">Home</a></li><li class="breadcrumb-item active"><?= e($title??'Dashboard') ?></li></ol></nav>
      </div>
    </div>
    <div class="tb-right">
      <div class="tb-btn"><i class="bi bi-bell"></i><span class="dot"></span></div>
      <div class="tb-admin dropdown" data-bs-toggle="dropdown">
        <div class="tb-av"><?= strtoupper(substr($admin['name']??'A',0,1)) ?></div>
        <div><div class="tb-aname"><?= e($admin['name']) ?></div><div class="tb-arole"><?= str_replace('_',' ',$admin['role']??'') ?></div></div>
        <i class="bi bi-chevron-down" style="font-size:10px;color:var(--muted);margin-left:4px;"></i>
      </div>
      <ul class="dropdown-menu dropdown-menu-end" style="border-radius:12px;border-color:var(--border);font-size:13px;min-width:150px;">
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>/logout"><i class="bi bi-box-arrow-left me-2"></i>Sign Out</a></li>
      </ul>
    </div>
  </div>

  <div class="page-body">
    <?php if(!empty($_SESSION['flash'])): $fl=$_SESSION['flash']; unset($_SESSION['flash']);
      $fc=$fl['type']==='success'?'#dcfce7':'#fee2e2'; $ft=$fl['type']==='success'?'#15803d':'#b91c1c';
      $fi=$fl['type']==='success'?'check-circle-fill':'exclamation-triangle-fill'; ?>
    <div id="flash-toast" style="background:<?= $fc ?>;color:<?= $ft ?>;">
      <i class="bi bi-<?= $fi ?>"></i><?= e($fl['message']) ?>
      <button onclick="this.closest('#flash-toast').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:20px;line-height:1;color:inherit;">&times;</button>
    </div>
    <script>
const CSRF_TOKEN = "<?= csrf_token() ?>";
const APP_URL = "<?= APP_URL ?>";
setTimeout(()=>{const t=document.getElementById('flash-toast');if(t){t.style.animation='toastOut .3s ease forwards';setTimeout(()=>t.remove(),300);}},4500);</script>
    <?php endif; ?>
    <?= $content ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.querySelectorAll('.toggle-status').forEach(el=>{
  el.addEventListener('change',function(){
    const orig=this.checked;
    fetch(this.dataset.url,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(d=>{if(!d.success)this.checked=!orig;}).catch(()=>this.checked=!orig);
  });
});
document.querySelectorAll('[data-confirm]').forEach(el=>{
  el.addEventListener('click',function(e){if(!confirm(this.dataset.confirm||'Are you sure?'))e.preventDefault();});
});
</script>
<?= $scripts ?? '' ?>
</body>
</html>
