document.addEventListener('DOMContentLoaded', function () {
  var scopeSelect = document.getElementById('scopeSelect');
  var scopeIdUser = document.getElementById('scopeIdUser');
  var scopeIdField = document.querySelector('[name="scope_id"]');

  function toggleScopeTarget() {
    if (!scopeSelect) return;
    var scope = scopeSelect.value;
    document.querySelectorAll('#scopeTargetWrap option[data-scope]').forEach(function (o) {
      o.style.display = o.dataset.scope === scope ? '' : 'none';
    });
    var wrap = document.getElementById('scopeTargetWrap');
    if (wrap) {
      wrap.style.display = ['seller', 'category', 'brand'].indexOf(scope) !== -1 ? '' : 'none';
    }
  }

  if (scopeSelect) {
    scopeSelect.addEventListener('change', toggleScopeTarget);
    toggleScopeTarget();
  }

  if (scopeIdUser && scopeIdField) {
    scopeIdUser.addEventListener('change', function () {
      scopeIdField.value = this.value;
    });
  }
});
