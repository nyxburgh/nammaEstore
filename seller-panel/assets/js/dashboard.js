document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('avatarToggle');
  const menu = document.getElementById('avatarMenu');
  if (!toggle || !menu) return;

  toggle.addEventListener('click', function (e) {
    e.stopPropagation();
    menu.classList.toggle('open');
  });

  document.addEventListener('click', function (e) {
    if (menu.classList.contains('open') && !menu.contains(e.target) && e.target !== toggle) {
      menu.classList.remove('open');
    }
  });
});
