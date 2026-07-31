/* Namma E Store — checkout page behaviour (extracted from checkout/index.php per the no-inline-JS coding rule) */

(function () {
  const form = document.getElementById('checkoutForm');
  if (!form) return;

  const APP_URL    = document.body.dataset.appUrl;
  const CSRF_TOKEN  = document.body.dataset.csrfToken;
  const BASE_SUBTOTAL = parseFloat(form.dataset.subtotal || '0');
  const BASE_SHIPPING = parseFloat(form.dataset.shipping || '0');
  const walletMax     = parseFloat(form.dataset.walletMax || '0');
  const loyaltyPointsAvailable = parseInt(form.dataset.loyaltyPoints || '0', 10);
  const loyaltyValueAvailable  = parseFloat(form.dataset.loyaltyValue || '0');

  let couponDiscount = 0, giftDiscount = 0, walletDiscount = 0, loyaltyDiscount = 0;

  function recalc() {
    let remaining = BASE_SUBTOTAL + BASE_SHIPPING - couponDiscount;
    giftDiscount = Math.min(giftDiscount, Math.max(0, remaining));
    remaining -= giftDiscount;

    const walletBox = document.getElementById('useWallet');
    walletDiscount = (walletBox && walletBox.checked) ? Math.min(walletMax, Math.max(0, remaining)) : 0;
    remaining -= walletDiscount;

    const loyaltyBox = document.getElementById('useLoyalty');
    loyaltyDiscount = (loyaltyBox && loyaltyBox.checked) ? Math.min(loyaltyValueAvailable, Math.max(0, remaining)) : 0;
    remaining -= loyaltyDiscount;

    document.getElementById('sumTotal').textContent = '₹' + remaining.toFixed(2);
    document.getElementById('walletAmountField').value = walletDiscount.toFixed(2);
    document.getElementById('loyaltyPointsField').value = loyaltyDiscount > 0 ? loyaltyPointsAvailable : 0;

    const walletRow = document.getElementById('walletRow');
    if (walletDiscount > 0) {
      walletRow.classList.remove('is-hidden');
      document.getElementById('walletAmt').textContent = '₹' + walletDiscount.toFixed(2);
    } else {
      walletRow.classList.add('is-hidden');
    }

    const loyaltyRow = document.getElementById('loyaltyRow');
    if (loyaltyDiscount > 0) {
      loyaltyRow.classList.remove('is-hidden');
      document.getElementById('loyaltyAmt').textContent = '₹' + loyaltyDiscount.toFixed(2);
    } else {
      loyaltyRow.classList.add('is-hidden');
    }
  }

  function setMsg(el, text, ok) {
    el.textContent = text;
    el.classList.toggle('msg-success', ok);
    el.classList.toggle('msg-error', !ok);
  }

  function applyCoupon() {
    const code = document.getElementById('couponInput').value.trim();
    if (!code) return;
    const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN); fd.append('code', code);
    fetch(APP_URL + '/checkout/apply-coupon', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
      const msg = document.getElementById('couponMsg');
      const row = document.getElementById('couponRow');
      if (d.success) {
        couponDiscount = d.discount;
        document.getElementById('couponCodeField').value = d.code;
        row.classList.remove('is-hidden');
        document.getElementById('couponAmt').textContent = '₹' + couponDiscount.toFixed(2);
        setMsg(msg, 'Coupon applied!', true);
      } else {
        couponDiscount = 0;
        document.getElementById('couponCodeField').value = '';
        row.classList.add('is-hidden');
        setMsg(msg, d.message, false);
      }
      recalc();
    });
  }

  function applyGiftCard() {
    const code = document.getElementById('giftInput').value.trim();
    if (!code) return;
    const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN); fd.append('code', code);
    fetch(APP_URL + '/checkout/apply-gift-card', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
      const msg = document.getElementById('giftMsg');
      const row = document.getElementById('giftRow');
      if (d.success) {
        giftDiscount = d.balance;
        document.getElementById('giftCodeField').value = code.toUpperCase();
        row.classList.remove('is-hidden');
        setMsg(msg, 'Gift card applied!', true);
        recalc();
        document.getElementById('giftAmt').textContent =
          '₹' + Math.min(giftDiscount, BASE_SUBTOTAL + BASE_SHIPPING - couponDiscount).toFixed(2);
      } else {
        giftDiscount = 0;
        document.getElementById('giftCodeField').value = '';
        row.classList.add('is-hidden');
        setMsg(msg, d.message, false);
        recalc();
      }
    });
  }

  function selectAddr(card) {
    document.querySelectorAll('.addr-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('f_name').value  = card.dataset.name;
    document.getElementById('f_phone').value = card.dataset.phone;
    document.getElementById('f_addr').value  = card.dataset.addr;
    document.getElementById('f_city').value  = card.dataset.city;
    document.getElementById('f_state').value = card.dataset.state;
    document.getElementById('f_pin').value   = card.dataset.pin;
  }

  function selectPayment(opt) {
    document.querySelectorAll('.payment-opt').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    opt.querySelector('input[type=radio]').checked = true;
  }

  // ── Delegated events, scoped to this page ─────────────────
  document.addEventListener('click', function (e) {
    const addr = e.target.closest('.addr-card');
    if (addr) { selectAddr(addr); return; }

    const pay = e.target.closest('.payment-opt');
    if (pay) { selectPayment(pay); return; }

    const action = e.target.closest('[data-action]');
    if (!action) return;
    switch (action.dataset.action) {
      case 'apply-coupon':    applyCoupon(); break;
      case 'apply-gift-card': applyGiftCard(); break;
    }
  });

  const walletBox = document.getElementById('useWallet');
  if (walletBox) walletBox.addEventListener('change', recalc);

  const loyaltyBox = document.getElementById('useLoyalty');
  if (loyaltyBox) loyaltyBox.addEventListener('change', recalc);

  form.addEventListener('submit', function () {
    const btn = document.getElementById('placeBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Placing Order...';
  });

  // Auto-select default address
  const firstAddr = document.querySelector('.addr-card');
  if (firstAddr) selectAddr(firstAddr);
})();
