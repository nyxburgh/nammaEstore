document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.module-check').forEach(function (cb) {
    cb.addEventListener('change', function () {
      var target = document.getElementById('p-' + this.dataset.module);
      if (target) target.style.display = this.checked ? 'block' : 'none';
    });
  });
});
