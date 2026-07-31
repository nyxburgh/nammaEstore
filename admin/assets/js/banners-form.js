document.addEventListener('DOMContentLoaded', function () {
  var input = document.querySelector('.banner-image-input');
  if (!input) return;

  input.addEventListener('change', function () {
    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        var pimg = document.getElementById('pimg');
        var prev = document.getElementById('prev');
        if (pimg) pimg.src = e.target.result;
        if (prev) prev.style.display = 'block';
      };
      reader.readAsDataURL(this.files[0]);
    }
  });
});
