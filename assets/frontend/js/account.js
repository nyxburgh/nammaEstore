/* Namma E Store — account sub-page behaviour: toggling the return/replacement
   request form (extracted from order-detail.php per the no-inline-JS rule) */

document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-action="show-return-form"]');
  if (btn) {
    const form = document.getElementById('ret-' + btn.dataset.itemId);
    if (form) form.classList.add('open');
    btn.classList.add('is-hidden');
    return;
  }

  const emailBtn = e.target.closest('[data-action="email-invoice"]');
  if (emailBtn) {
    const originalText = emailBtn.textContent;
    emailBtn.disabled = true;
    emailBtn.textContent = 'Sending...';
    const fd = new FormData();
    fd.append('_csrf_token', document.body.dataset.csrfToken);
    fetch(document.body.dataset.appUrl + '/invoices/' + emailBtn.dataset.invoiceId + '/email', { method: 'POST', body: fd })
      .then(r => r.json()).then(d => {
        alert(d.message);
        emailBtn.disabled = false;
        emailBtn.textContent = originalText;
      });
    return;
  }

  const notif = e.target.closest('[data-action="read-notification"]');
  if (notif) {
    const fd = new FormData();
    fd.append('_csrf_token', document.body.dataset.csrfToken);
    fetch(document.body.dataset.appUrl + '/account/notifications/' + notif.dataset.notifId + '/read', { method: 'POST', body: fd })
      .then(() => {
        notif.classList.remove('unread');
        const link = notif.dataset.link;
        if (link) location.href = link;
      });
    return;
  }

  const el = e.target.closest('[data-action]');
  if (!el) return;
  switch (el.dataset.action) {
    // Addresses page
    case 'addr-default': {
      if (!confirm('Set as default address?')) return;
      const fd = new FormData();
      fd.append('_csrf_token', document.body.dataset.csrfToken);
      fetch(document.body.dataset.appUrl + '/account/addresses/' + el.dataset.addrId + '/default', { method: 'POST', body: fd })
        .then(r => r.json()).then(() => location.reload());
      break;
    }
    case 'addr-delete': {
      if (!confirm('Delete this address?')) return;
      const fd = new FormData();
      fd.append('_csrf_token', document.body.dataset.csrfToken);
      fetch(document.body.dataset.appUrl + '/account/addresses/' + el.dataset.addrId + '/delete', { method: 'POST', body: fd })
        .then(r => r.json()).then(() => location.reload());
      break;
    }
    case 'modal-open':  document.getElementById(el.dataset.target).classList.add('open'); break;
    case 'modal-close': document.getElementById(el.dataset.target).classList.remove('open'); break;
    // Wishlist page
    case 'remove-wish':
      toggleWishlist(el.dataset.productId, el);
      el.closest('.wish-card').classList.add('fading');
      setTimeout(() => location.reload(), 800);
      break;
    // Profile page
    case 'account-delete-note': showToast('⚠️ Please contact support to delete your account.'); break;
  }
});
