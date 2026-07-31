/* Namma E Store — order tracking page behaviour (extracted from orders/track.php per the no-inline-JS coding rule) */

document.addEventListener('submit', function (e) {
  const form = e.target.closest('[data-action="track-order"]');
  if (!form) return;
  e.preventDefault();
  const num = document.getElementById('trackNum').value.trim();
  if (!num) { showToast('⚠️ Enter an order number'); return; }
  location.href = document.body.dataset.appUrl + '/track/' + encodeURIComponent(num);
});
