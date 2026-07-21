<?php $p=ADMIN_URL; ?>
<div class="mb-4"><h5 style="font-weight:800;margin:0;">Reports & Analytics</h5></div>
<div class="row g-3">
  <?php foreach([['Sales Report','Daily revenue & order trends','graph-up-arrow','purple',$p.'/reports/sales'],['Commission Report','Per-seller commission by period','percent','pink',$p.'/reports/commission'],['Seller Report','Seller performance & earnings','shop','green',$p.'/reports/sellers']] as [$t,$d,$i,$c,$u]): ?>
  <div class="col-md-4"><a href="<?= $u ?>" style="text-decoration:none;"><div class="card" style="transition:all .2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="card-body" style="padding:28px;"><div class="si <?= $c ?>" style="width:50px;height:50px;border-radius:13px;font-size:21px;margin-bottom:16px;"><i class="bi bi-<?= $i ?>"></i></div><h6 style="font-weight:800;margin-bottom:6px;"><?= $t ?></h6><p style="font-size:13px;color:var(--muted);margin:0;"><?= $d ?></p></div>
  </div></a></div>
  <?php endforeach; ?>
</div>
