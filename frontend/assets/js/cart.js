/* Namma E Store — cart page behaviour (extracted from cart/index.php per the no-inline-JS coding rule) */

(function () {
  function cartQty(itemId, delta) {
    const span = document.getElementById('qty-' + itemId);
    let qty = parseInt(span.textContent, 10) + delta;
    if (qty < 1) qty = 1;
    span.textContent = qty;
    const fd = new FormData();
    fd.append('item_id', itemId); fd.append('qty', qty); fd.append('_csrf_token', CSRF_TOKEN);
    fetch(APP_URL + '/cart/update', { method: 'POST', body: fd })
      .then(r => r.json()).then(d => updateCartBadge(d.count));
    setTimeout(() => location.reload(), 500);
  }

  function removeItem(itemId) {
    if (!confirm('Remove this item?')) return;
    const fd = new FormData();
    fd.append('item_id', itemId); fd.append('_csrf_token', CSRF_TOKEN);
    fetch(APP_URL + '/cart/remove', { method: 'POST', body: fd })
      .then(r => r.json()).then(d => { updateCartBadge(d.count); location.reload(); });
  }

  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-action]');
    if (!el) return;
    switch (el.dataset.action) {
      case 'cart-qty':    cartQty(el.dataset.itemId, parseInt(el.dataset.delta, 10)); break;
      case 'cart-remove': removeItem(el.dataset.itemId); break;
      case 'coupon-demo': showToast('🎫 Coupon applied!'); break;
    }
  });
})();
