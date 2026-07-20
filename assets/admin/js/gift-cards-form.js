document.addEventListener('DOMContentLoaded', function () {
  var typeSelect = document.getElementById('typeSelect');
  var vendorWrap = document.getElementById('vendorWrap');
  if (!typeSelect || !vendorWrap) return;

  typeSelect.addEventListener('change', function () {
    vendorWrap.style.display = this.value === 'vendor' ? '' : 'none';
  });
});
