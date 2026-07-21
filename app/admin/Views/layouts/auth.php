<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($title??'Login') ?> — Namma E Store Admin</title>
<link href="<?= admin_asset('seller/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f0224 0%,#1e0845 55%,#2d0a1f 100%);display:flex;align-items:center;justify-content:center;padding:20px;}
body::before{content:'';position:fixed;top:-150px;right:-150px;width:450px;height:450px;border-radius:50%;background:radial-gradient(circle,rgba(233,30,140,.2),transparent 70%);pointer-events:none;}
body::after{content:'';position:fixed;bottom:-150px;left:-150px;width:450px;height:450px;border-radius:50%;background:radial-gradient(circle,rgba(109,40,217,.25),transparent 70%);pointer-events:none;}
.card{width:100%;max-width:420px;background:rgba(255,255,255,.07);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.12);border-radius:22px;padding:44px 40px;position:relative;z-index:1;}
.brand{text-align:center;margin-bottom:36px;}
.logo{width:58px;height:58px;border-radius:18px;background:linear-gradient(135deg,#6d28d9,#e91e8c);margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;box-shadow:0 8px 28px rgba(233,30,140,.4);}
.brand h1{font-size:22px;font-weight:800;color:#fff;margin-bottom:5px;}
.brand p{font-size:13px;color:rgba(255,255,255,.4);}
.form-group{margin-bottom:20px;}
label{display:block;font-size:12px;font-weight:700;color:rgba(255,255,255,.55);margin-bottom:8px;letter-spacing:.4px;text-transform:uppercase;}
.input-wrap{position:relative;}
.input-wrap i.icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.28);font-size:15px;}
input[type=email],input[type=password]{width:100%;padding:12px 14px 12px 42px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);border-radius:11px;color:#fff;font-size:14px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;}
input::placeholder{color:rgba(255,255,255,.22);}
input:focus{border-color:rgba(139,92,246,.7);box-shadow:0 0 0 3px rgba(109,40,217,.2);}
.eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.3);cursor:pointer;font-size:15px;padding:4px;}
.eye-btn:hover{color:rgba(255,255,255,.7);}
.btn-submit{width:100%;padding:14px;margin-top:4px;background:linear-gradient(135deg,#6d28d9,#e91e8c);border:none;border-radius:12px;color:#fff;font-size:15px;font-weight:700;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;cursor:pointer;box-shadow:0 6px 22px rgba(233,30,140,.35);transition:filter .2s,transform .15s;}
.btn-submit:hover{filter:brightness(1.08);transform:translateY(-1px);}
.alert{display:flex;align-items:center;gap:10px;background:rgba(220,38,38,.15);border:1px solid rgba(220,38,38,.25);border-radius:10px;padding:11px 14px;margin-bottom:20px;color:#fca5a5;font-size:13px;}
.alert.success{background:rgba(22,163,74,.15);border-color:rgba(22,163,74,.25);color:#86efac;}
.footer{text-align:center;margin-top:28px;font-size:12px;color:rgba(255,255,255,.2);}
input.is-invalid{border-color:rgba(220,38,38,.6)!important;box-shadow:0 0 0 3px rgba(220,38,38,.15)!important;}
.invalid-feedback{display:none;color:#fca5a5;font-size:12px;margin-top:6px;}
.is-invalid ~ .invalid-feedback{display:block;}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="logo">NES</div>
    <h1>Namma E Store Admin</h1>
    <p>Sign in to manage your marketplace</p>
  </div>
  <?php if(!empty($_SESSION['flash'])): $fl=$_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert <?= $fl['type']==='success'?'success':'' ?>">
    <i class="bi bi-<?= $fl['type']==='success'?'check-circle-fill':'exclamation-triangle-fill' ?>"></i>
    <?= e($fl['message']) ?>
  </div>
  <?php endif; ?>
  <?= $content ?>
  <div class="footer">&copy; <?= date('Y') ?> Namma E Store Marketplace</div>
</div>
<script src="<?= admin_asset('js/form-validation.js') ?>"></script>
<script>
function togglePwd(btn){const i=btn.closest('.input-wrap').querySelector('input'),ic=btn.querySelector('i');i.type=i.type==='password'?'text':'password';ic.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';}
</script>
</body>
</html>
