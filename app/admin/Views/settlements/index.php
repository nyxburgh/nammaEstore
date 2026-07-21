<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Settlement Engine</h5><small class="text-muted">Seller payout waterfall &amp; wallet crediting</small></div>
  <a href="<?= $p ?>/settlements/withdrawals" class="btn btn-outline-primary btn-sm">View Withdrawal Requests</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <p class="text-muted" style="font-size:13px;">
      The settlement engine evaluates every <strong>delivered &amp; paid</strong> order-seller split against the
      eligibility gate — return window closed, no open return/replacement request, no open dispute, and (for
      subscription-plan sellers) an active subscription — then computes the deduction waterfall
      (Gross − Shipping − Platform Charge − Commission − Penalty − Refund − Subscription Due) before crediting
      the seller's wallet.
    </p>
    <div class="d-flex gap-2">
      <form method="POST" action="<?= $p ?>/settlements/run" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-arrow-repeat"></i> Run Eligibility Pass
        </button>
      </form>
      <form method="POST" action="<?= $p ?>/settlements/credit" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-success">
          <i class="bi bi-wallet2"></i> Credit Eligible to Wallets
        </button>
      </form>
    </div>
    <p class="text-muted mt-2" style="font-size:11.5px;">
      Both actions are safe to run repeatedly — already-settled splits are skipped. In production, schedule
      "Run Eligibility Pass" as a daily cron job; "Credit Eligible to Wallets" can run right after it, or be
      left for manual review first.
    </p>
  </div>
</div>

<div class="row g-2">
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <h6 style="font-weight:700;">📋 Returns &amp; Replacements</h6>
      <p class="text-muted" style="font-size:12.5px;">Open requests hold settlement until resolved.</p>
      <a href="<?= $p ?>/returns" class="btn btn-sm btn-outline-secondary">Review Queue</a>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <h6 style="font-weight:700;">⚖️ Disputes</h6>
      <p class="text-muted" style="font-size:12.5px;">Open disputes block settlement for the affected order.</p>
      <a href="<?= $p ?>/disputes" class="btn btn-sm btn-outline-secondary">Review Queue</a>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <h6 style="font-weight:700;">💸 Withdrawal Requests</h6>
      <p class="text-muted" style="font-size:12.5px;">Seller payout requests awaiting approval.</p>
      <a href="<?= $p ?>/settlements/withdrawals" class="btn btn-sm btn-outline-secondary">Review Queue</a>
    </div></div>
  </div>
</div>
