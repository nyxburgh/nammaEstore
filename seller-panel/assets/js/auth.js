/* Namma E Store Seller Panel — auth page behaviour (login/register password visibility toggle) */

document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-action="toggle-password"]');
  if (!btn) return;
  var input = document.getElementById(btn.dataset.target);
  if (!input) return;
  var showing = input.type === 'text';
  input.type = showing ? 'password' : 'text';
  btn.textContent = showing ? '👁️' : '🙈';
  btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
});
