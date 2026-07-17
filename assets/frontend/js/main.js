/* Namma E Store — shared storefront behaviour (extracted from layouts/main.php per the no-inline-JS coding rule) */

const APP_URL    = document.body.dataset.appUrl;
const CSRF_TOKEN  = document.body.dataset.csrfToken;

// ── Cart State ────────────────────────────────────────────
function loadCart() {
  fetch(APP_URL + '/cart/count')
    .then(r => r.json()).then(d => updateCartBadge(d.count));
}

function updateCartBadge(n) {
  ['cartBadgeTop', 'cartBadgeMob', 'cartCount'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = n > 0 ? n : '';
  });
}

function openCart() {
  document.getElementById('cartDrawer').classList.add('open');
  document.getElementById('cartOverlay').classList.add('open');
  document.body.classList.add('no-scroll');
  fetchCartItems();
}
function closeCart() {
  document.getElementById('cartDrawer').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('open');
  document.body.classList.remove('no-scroll');
}

function fetchCartItems() {
  fetch(APP_URL + '/cart/count').then(r => r.json()).then(d => {
    const footer = document.getElementById('cartFooter');
    if (d.count === 0) {
      document.getElementById('cartBody').innerHTML =
        '<div class="cart-empty"><div class="ce-icon">🛒</div><p>Your cart is empty</p>' +
        '<a href="' + APP_URL + '/products" class="btn-shop" data-action="close-cart">Start Shopping →</a></div>';
      footer.classList.add('is-hidden');
    } else {
      footer.classList.remove('is-hidden');
      document.getElementById('cartCount').textContent = d.count;
    }
  });
}

function addToCart(productId, qty = 1, variantId = null) {
  const fd = new FormData();
  fd.append('product_id', productId);
  fd.append('_csrf_token', CSRF_TOKEN);
  fd.append('qty', qty);
  if (variantId) fd.append('variant_id', variantId);
  fetch(APP_URL + '/cart/add', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => {
      if (d.success) { updateCartBadge(d.count); showToast('🛒 Added to cart!'); openCart(); }
      else showToast('⚠️ ' + d.message);
    });
}

function removeCartItem(itemId) {
  const fd = new FormData(); fd.append('item_id', itemId);
  fetch(APP_URL + '/cart/remove', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => { updateCartBadge(d.count); location.href = location.href; });
}

function updateCartQty(itemId, qty) {
  const fd = new FormData(); fd.append('item_id', itemId); fd.append('qty', qty);
  fetch(APP_URL + '/cart/update', { method: 'POST', body: fd }).then(r => r.json()).then(d => updateCartBadge(d.count));
}

function toggleWishlist(productId, btn) {
  const fd = new FormData(); fd.append('product_id', productId);
  fd.append('_csrf_token', CSRF_TOKEN);
  fetch(APP_URL + '/wishlist/toggle', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => {
      if (!d.success && d.redirect) { location.href = d.redirect; return; }
      if (btn) btn.textContent = d.added ? '❤️' : '🤍';
      showToast(d.added ? '❤️ Added to wishlist!' : '💔 Removed from wishlist');
    });
}

// ── Toast ─────────────────────────────────────────────────
let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  t.innerHTML = msg; t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2500);
}

// ── Account Dropdown ──────────────────────────────────────
// `btn` is the [data-action] element resolved by the delegated
// listener — inside that listener e.currentTarget is `document`,
// so the button must be passed in explicitly.
function toggleDeskAccount(btn) {
  const dd = document.getElementById('deskAccDropdown');
  const rect = btn.getBoundingClientRect();
  dd.style.top = rect.bottom + 'px';
  dd.classList.toggle('open');
}
document.addEventListener('click', function (e) {
  const dd = document.getElementById('deskAccDropdown');
  if (!dd || !dd.classList.contains('open')) return;
  const path = e.composedPath ? e.composedPath() : [];
  if (!path.some(el => el && (el.id === 'deskAccDropdown' || (el.classList && el.classList.contains('desk-account-wrap')))))
    dd.classList.remove('open');
});

// ── Mobile Popups ─────────────────────────────────────────
function closeAllPopups() {
  document.getElementById('mobAccPopup').classList.remove('open');
  document.getElementById('mobCatsPopup').classList.remove('open');
  const menu = document.getElementById('mobMenuDrawer');
  if (menu) menu.classList.remove('open');
  document.getElementById('mobOverlay').classList.remove('open');
  document.body.classList.remove('no-scroll');
}
function toggleMenu() {
  const menu = document.getElementById('mobMenuDrawer'), ov = document.getElementById('mobOverlay');
  document.getElementById('mobAccPopup').classList.remove('open');
  document.getElementById('mobCatsPopup').classList.remove('open');
  if (menu.classList.contains('open')) { closeAllPopups(); }
  else { menu.classList.add('open'); ov.classList.add('open'); document.body.classList.add('no-scroll'); }
}
function toggleAccount() {
  const acc = document.getElementById('mobAccPopup'), ov = document.getElementById('mobOverlay');
  document.getElementById('mobCatsPopup').classList.remove('open');
  if (acc.classList.contains('open')) { closeAllPopups(); }
  else { acc.classList.add('open'); ov.classList.add('open'); document.body.classList.add('no-scroll'); }
}
function toggleCats() {
  const cats = document.getElementById('mobCatsPopup'), ov = document.getElementById('mobOverlay');
  document.getElementById('mobAccPopup').classList.remove('open');
  if (cats.classList.contains('open')) { closeAllPopups(); }
  else { cats.classList.add('open'); ov.classList.add('open'); document.body.classList.add('no-scroll'); }
}

// ── Back to Top ───────────────────────────────────────────
window.addEventListener('scroll', () => document.getElementById('backTop').classList.toggle('show', window.scrollY > 400));

// ── Scroll Reveal ─────────────────────────────────────────
const revObs = new IntersectionObserver(entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => revObs.observe(el));

// ── PWA: service worker registration ───────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register(APP_URL + '/sw.js').catch(() => {
      // Silently ignore — PWA install just won't be offered, site works fine either way.
    });
  });
}

// ── Delegated click handling ───────────────────────────────
// Replaces every onclick="..." attribute that used to live in the
// HTML (layout, and JS-generated markup like the empty-cart message)
// with a single delegated listener keyed off data-action, per the
// no-inline-JS coding rule.
document.addEventListener('click', function (e) {
  const el = e.target.closest('[data-action]');
  if (!el) return;
  const action = el.dataset.action;

  switch (action) {
    case 'toggle-desk-account': toggleDeskAccount(el); break;
    case 'open-cart':           openCart(); break;
    case 'close-cart':          closeCart(); break;
    case 'close-all-popups':    closeAllPopups(); break;
    case 'toggle-cats':         toggleCats(); break;
    case 'toggle-account':      toggleAccount(); break;
    case 'toggle-menu':         toggleMenu(); break;
    case 'scroll-top':          window.scrollTo({ top: 0, behavior: 'smooth' }); break;
    case 'remove-cart-item':    removeCartItem(el.dataset.itemId); break;
    case 'update-cart-qty':     updateCartQty(el.dataset.itemId, el.dataset.qty); break;
    case 'add-to-cart':         addToCart(el.dataset.productId, el.dataset.qty || 1, el.dataset.variantId || null); break;
    case 'toggle-wishlist':     toggleWishlist(el.dataset.productId, el); break;
    // Navigate a whole card acting as a link — ignore clicks on real controls inside it
    case 'go':                  if (!e.target.closest('button, a, input, select')) location.href = el.dataset.href; break;
  }
});

// Forms that auto-submit when a control changes (e.g. the sort dropdown)
document.addEventListener('change', function (e) {
  const el = e.target.closest('[data-action="submit-on-change"]');
  if (el && el.form) el.form.submit();
});
