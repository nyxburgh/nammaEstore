/* Namma E Store — product detail page behaviour (extracted from products/show.php per the no-inline-JS coding rule) */

(function () {
  const page = document.getElementById('productPage');
  if (!page) return;

  const productId = parseInt(page.dataset.productId, 10);
  const maxStock  = parseInt(page.dataset.maxStock, 10) || 1;
  let selectedVariant = null;

  function switchImg(thumb) {
    const mi = document.getElementById('mainImg');
    if (mi) mi.src = thumb.dataset.src;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
  }

  function switchTab(btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.tab).classList.add('active');
  }

  function changeQty(delta) {
    const inp = document.getElementById('qtyInput');
    let v = (parseInt(inp.value, 10) || 1) + delta;
    if (v < 1) v = 1;
    if (v > maxStock) v = maxStock;
    inp.value = v;
  }

  function selectVariant(btn) {
    selectedVariant = parseInt(btn.dataset.variant, 10);
    document.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }

  function addToCartFromDetail() {
    const qty = parseInt(document.getElementById('qtyInput').value, 10) || 1;
    addToCart(productId, qty, selectedVariant);
  }

  function setRating(n) {
    const val = document.getElementById('ratingVal');
    if (!val) return;
    val.value = n;
    document.querySelectorAll('#starPicker .sp-star').forEach((s, i) => s.classList.toggle('on', i < n));
  }

  function submitReview() {
    const rating = parseInt(document.getElementById('ratingVal').value, 10) || 5;
    const title  = document.getElementById('reviewTitle')?.value || '';
    const body   = document.getElementById('reviewBody')?.value || '';
    if (!body.trim()) { showToast('⚠️ Please write your review.'); return; }
    const fd = new FormData();
    fd.append('rating', rating); fd.append('title', title); fd.append('body', body);
    fd.append('_csrf_token', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
    fetch(APP_URL + '/product/' + productId + '/review', { method: 'POST', body: fd })
      .then(r => r.json()).then(d => {
        showToast(d.success ? '✅ ' + d.message : '⚠️ ' + d.message);
        if (d.success) {
          document.getElementById('reviewBody').value = '';
          document.getElementById('reviewTitle').value = '';
          setRating(5);
        }
      }).catch(() => showToast('⚠️ Failed to submit review.'));
  }

  // Rating distribution bars — width set from data-pct, keeping the markup style-free
  document.querySelectorAll('.rv-bar-fill[data-pct]').forEach(bar => { bar.style.width = bar.dataset.pct + '%'; });
  setRating(5);

  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-action]');
    if (!el) return;
    switch (el.dataset.action) {
      case 'switch-img':      switchImg(el); break;
      case 'switch-tab':      switchTab(el); break;
      case 'change-qty':      changeQty(parseInt(el.dataset.delta, 10)); break;
      case 'select-variant':  selectVariant(el); break;
      case 'add-detail':      addToCartFromDetail(); break;
      case 'buy-now':         addToCartFromDetail(); setTimeout(() => location.href = APP_URL + '/checkout', 600); break;
      case 'set-rating':      setRating(parseInt(el.dataset.star, 10)); break;
      case 'submit-review':   submitReview(); break;
    }
  });
})();
