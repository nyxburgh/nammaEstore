<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/banners" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><h5 style="font-weight:800;margin:0;">Add Banner</h5></div>
<div class="row"><div class="col-lg-7"><div class="card"><div class="card-header"><span class="card-title">Banner Details</span></div><div class="card-body">
<form method="POST" action="<?= $p ?>/banners" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required value="<?= e($_POST['title']??'') ?>"></div>
  <div class="mb-3"><label class="form-label">Image * <span style="color:var(--muted);font-size:12px;">(JPG/PNG/WebP — 1200×400px recommended)</span></label>
    <input type="file" name="image" class="form-control" accept="image/*" required onchange="prevImg(this)">
    <div id="prev" style="display:none;margin-top:8px;"><img id="pimg" src="" style="max-width:100%;border-radius:8px;max-height:160px;object-fit:cover;border:1px solid var(--border);"></div>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Position *</label><select name="position" class="form-select" required><option value="hero">Hero (Main Slider)</option><option value="sidebar">Sidebar</option><option value="popup">Popup</option><option value="top_bar">Top Bar</option></select></div>
    <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
  </div>
  <div class="mb-3"><label class="form-label">Link URL <span style="color:var(--muted);font-size:12px;">(optional)</span></label><input type="url" name="link_url" class="form-control" placeholder="https://..."></div>
  <div class="row g-3 mb-4">
    <div class="col-md-6"><label class="form-label">Start Date</label><input type="datetime-local" name="starts_at" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">End Date</label><input type="datetime-local" name="ends_at" class="form-control"></div>
  </div>
  <div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4">Upload Banner</button><a href="<?= $p ?>/banners" class="btn btn-outline-secondary">Cancel</a></div>
</form>
</div></div></div></div>
<?php $scripts='<script>function prevImg(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{document.getElementById("pimg").src=e.target.result;document.getElementById("prev").style.display="block";};r.readAsDataURL(i.files[0]);}}</script>'; ?>
