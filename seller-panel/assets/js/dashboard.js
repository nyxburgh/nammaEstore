document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('avatarToggle');
  const menu = document.getElementById('avatarMenu');
  if (toggle && menu) {
    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      menu.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
      if (menu.classList.contains('open') && !menu.contains(e.target) && e.target !== toggle) {
        menu.classList.remove('open');
      }
    });
  }

  const notifToggle = document.getElementById('notifToggle');
  const notifPanel = document.getElementById('notifPanel');
  if (notifToggle && notifPanel) {
    notifToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      notifPanel.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
      if (notifPanel.classList.contains('open') && !notifPanel.contains(e.target) && e.target !== notifToggle) {
        notifPanel.classList.remove('open');
      }
    });
  }

  const notifMarkAll = document.getElementById('notifMarkAll');
  if (notifMarkAll) {
    notifMarkAll.addEventListener('click', function () {
      fetch(SELLER_URL + '/notifications/mark-all-read', {
        method: 'POST',
        body: new URLSearchParams({ _csrf_token: CSRF_TOKEN })
      }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
      });
    });
  }
});
