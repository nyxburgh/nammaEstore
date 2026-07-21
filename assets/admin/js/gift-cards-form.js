document.addEventListener('DOMContentLoaded', function () {
  var typeSelect = document.getElementById('typeSelect');
  var sellerWrap = document.getElementById('sellerWrap');
  if (!typeSelect || !sellerWrap) return;

  typeSelect.addEventListener('change', function () {
    sellerWrap.style.display = this.value === 'seller' ? '' : 'none';
  });
});
