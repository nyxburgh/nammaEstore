/* Namma E Store — payment gateway page behaviour (extracted from checkout/payment.php per the no-inline-JS coding rule) */

(function () {
  const cfgEl = document.getElementById('rzpConfig');
  const btn   = document.getElementById('payBtn');
  if (!cfgEl || !btn || typeof Razorpay === 'undefined') return;

  const cfg = JSON.parse(cfgEl.textContent);
  cfg.handler = function (resp) {
    const f = document.getElementById('cbForm');
    f.razorpay_payment_id.value = resp.razorpay_payment_id;
    f.razorpay_signature.value  = resp.razorpay_signature;
    f.submit();
  };
  const rzp = new Razorpay(cfg);
  btn.addEventListener('click', () => rzp.open());
})();
