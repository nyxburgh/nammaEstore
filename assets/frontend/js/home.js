/* Namma E Store — home page behaviour (extracted from home/index.php per the no-inline-JS coding rule) */

(function () {
  const slides = document.getElementById('heroSlides');
  if (!slides) return;

  const dots = document.querySelectorAll('.hero-dot');
  let slide = 0;

  function goSlide(n) {
    slide = n;
    slides.style.transform = 'translateX(-' + n * 33.333 + '%)';
    dots.forEach((d, i) => d.classList.toggle('active', i === n));
  }
  setInterval(() => goSlide((slide + 1) % 3), 4000);

  // Flash-sale countdown
  let secs = 4 * 3600 + 27 * 60 + 45;
  setInterval(function () {
    if (secs <= 0) return;
    secs--;
    const h = String(Math.floor(secs / 3600)).padStart(2, '0');
    const m = String(Math.floor((secs % 3600) / 60)).padStart(2, '0');
    const s = String(secs % 60).padStart(2, '0');
    document.getElementById('ch').textContent = h;
    document.getElementById('cm').textContent = m;
    document.getElementById('cs').textContent = s;
  }, 1000);

  // Deal progress bars — width set from data-pct, keeping the markup style-free
  document.querySelectorAll('.progress-fill[data-pct]').forEach(bar => { bar.style.width = bar.dataset.pct + '%'; });

  function subscribeNewsletter(btn) {
    const inp   = btn.previousElementSibling;
    const email = inp ? inp.value.trim() : '';
    if (!email) { showToast('⚠️ Enter your email'); return; }
    const fd = new FormData();
    fd.append('email', email);
    fd.append('_csrf_token', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
    btn.textContent = '⏳ Subscribing...';
    btn.disabled    = true;
    fetch(APP_URL + '/subscribe', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        showToast(d.success ? '✅ ' + d.message : '⚠️ ' + d.message);
        if (d.success) inp.value = '';
        btn.textContent = 'Subscribe →';
        btn.disabled    = false;
      })
      .catch(() => { btn.textContent = 'Subscribe →'; btn.disabled = false; });
  }

  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-action]');
    if (!el) return;
    switch (el.dataset.action) {
      case 'go-slide':             goSlide(parseInt(el.dataset.slide, 10)); break;
      case 'subscribe-newsletter': subscribeNewsletter(el); break;
    }
  });
})();
