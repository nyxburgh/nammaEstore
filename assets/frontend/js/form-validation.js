/* Namma E Store — shared real-time client-side form validation (storefront panel).
 * This is the ONE allowed inline-JS pattern in this repo (see CLAUDE.md): views wire
 * fields with oninput="validateField(this)" onblur="validateField(this)" and forms with
 * onsubmit="return validateForm(this)" — the actual logic lives here, in one shared file.
 *
 * Rules:
 *  - Never clear/reset a field's value on invalid — only toggle classes + error text.
 *  - validateField(el) validates one control, returns true/false.
 *  - validateForm(form) validates every control in the form, returns true/false.
 */
(function () {
  'use strict';

  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  var PHONE_RE = /^\+?[0-9 \-]{7,15}$/;

  function isValidatable(el) {
    if (!el || !el.tagName) return false;
    var tag = el.tagName.toLowerCase();
    if (tag !== 'input' && tag !== 'select' && tag !== 'textarea') return false;
    var type = (el.type || '').toLowerCase();
    if (['submit', 'button', 'reset', 'hidden'].indexOf(type) !== -1) return false;
    if (el.disabled) return false;
    return true;
  }

  function getErrorEl(el) {
    var next = el.nextElementSibling;
    if (next && next.classList && next.classList.contains('field-error')) {
      return next;
    }
    var err = document.createElement('small');
    err.className = 'field-error';
    if (el.parentNode) {
      el.parentNode.insertBefore(err, el.nextSibling);
    }
    return err;
  }

  function setInvalid(el, message) {
    el.classList.add('is-invalid');
    el.setAttribute('aria-invalid', 'true');
    var errEl = getErrorEl(el);
    errEl.textContent = message;
    errEl.classList.add('is-visible');
    return false;
  }

  function setValid(el) {
    el.classList.remove('is-invalid');
    el.setAttribute('aria-invalid', 'false');
    var next = el.nextElementSibling;
    if (next && next.classList && next.classList.contains('field-error')) {
      next.textContent = '';
      next.classList.remove('is-visible');
    }
    return true;
  }

  function fieldByRef(el, ref) {
    if (!ref) return null;
    try {
      if (ref.charAt(0) === '#') {
        return document.getElementById(ref.slice(1));
      }
      var form = el.form || el.closest('form');
      if (form) {
        var found = form.querySelector(ref);
        if (found) return found;
      }
      return document.querySelector(ref);
    } catch (e) {
      return null;
    }
  }

  function checkRadioGroupChecked(el) {
    var name = el.name;
    if (!name) return el.checked;
    var form = el.form || document;
    var group = form.querySelectorAll('input[type="radio"][name="' + cssEscape(name) + '"]');
    for (var i = 0; i < group.length; i++) {
      if (group[i].checked) return true;
    }
    return false;
  }

  function cssEscape(value) {
    if (window.CSS && CSS.escape) return CSS.escape(value);
    return String(value).replace(/([^\w-])/g, '\\$1');
  }

  function isEmptyDateNumberValid(min, max, value, isDate) {
    if (min !== null && min !== '' && value < min) return false;
    if (max !== null && max !== '' && value > max) return false;
    return true;
  }

  /**
   * Validate a single field, update its UI, and return true/false.
   */
  window.validateField = function (el) {
    if (!el || el.disabled) return true;

    var type = (el.type || '').toLowerCase();
    var required = el.hasAttribute('required');

    // Radio: validate the whole group under the first radio's error slot,
    // but only enforce "required" (group-level check).
    if (type === 'radio') {
      if (required && !checkRadioGroupChecked(el)) {
        return setInvalid(el, 'Please select an option.');
      }
      return setValid(el);
    }

    if (type === 'checkbox') {
      if (required && !el.checked) {
        return setInvalid(el, 'This field is required.');
      }
      return setValid(el);
    }

    if (type === 'file') {
      if (required && (!el.files || el.files.length === 0)) {
        return setInvalid(el, 'Please choose a file.');
      }
      return setValid(el);
    }

    var value = el.value != null ? el.value : '';
    var trimmed = value.trim();

    if (required && trimmed === '') {
      return setInvalid(el, 'This field is required.');
    }

    // If not required and empty, nothing further to check.
    if (trimmed === '') {
      return setValid(el);
    }

    if (type === 'email') {
      if (!EMAIL_RE.test(trimmed)) {
        return setInvalid(el, 'Enter a valid email address.');
      }
    }

    if (type === 'tel') {
      if (el.getAttribute('pattern')) {
        var telPattern = new RegExp('^(?:' + el.getAttribute('pattern') + ')$');
        if (!telPattern.test(trimmed)) {
          return setInvalid(el, el.getAttribute('data-pattern-message') || 'Enter a valid phone number.');
        }
      } else if (!PHONE_RE.test(trimmed)) {
        return setInvalid(el, 'Enter a valid phone number.');
      }
    }

    if (type === 'url') {
      try {
        new URL(trimmed);
      } catch (e) {
        return setInvalid(el, 'Enter a valid URL.');
      }
    }

    if (type === 'number') {
      var numVal = parseFloat(trimmed);
      if (isNaN(numVal)) {
        return setInvalid(el, 'Enter a valid number.');
      }
      var min = el.getAttribute('min');
      var max = el.getAttribute('max');
      if (min !== null && min !== '' && numVal < parseFloat(min)) {
        return setInvalid(el, 'Value must be at least ' + min + '.');
      }
      if (max !== null && max !== '' && numVal > parseFloat(max)) {
        return setInvalid(el, 'Value must be at most ' + max + '.');
      }
    }

    if (type === 'date' || type === 'datetime-local') {
      var min2 = el.getAttribute('min');
      var max2 = el.getAttribute('max');
      if (min2 && trimmed < min2) {
        return setInvalid(el, 'Date must be on or after ' + min2 + '.');
      }
      if (max2 && trimmed > max2) {
        return setInvalid(el, 'Date must be on or before ' + max2 + '.');
      }
    }

    var minlength = el.getAttribute('minlength');
    if (minlength !== null && trimmed.length < parseInt(minlength, 10)) {
      return setInvalid(el, 'Must be at least ' + minlength + ' characters.');
    }

    var maxlength = el.getAttribute('maxlength');
    if (maxlength !== null && trimmed.length > parseInt(maxlength, 10)) {
      return setInvalid(el, 'Must be at most ' + maxlength + ' characters.');
    }

    var dataPattern = el.getAttribute('data-pattern');
    if (dataPattern) {
      var customRe;
      try {
        customRe = new RegExp('^(?:' + dataPattern + ')$');
      } catch (e) {
        customRe = null;
      }
      if (customRe && !customRe.test(trimmed)) {
        var customMsg = el.getAttribute('data-pattern-message') || 'Value does not match the required format.';
        return setInvalid(el, customMsg);
      }
    } else {
      var pattern = el.getAttribute('pattern');
      if (pattern && type !== 'tel') {
        var nativeRe;
        try {
          nativeRe = new RegExp('^(?:' + pattern + ')$');
        } catch (e) {
          nativeRe = null;
        }
        if (nativeRe && !nativeRe.test(trimmed)) {
          return setInvalid(el, el.getAttribute('data-pattern-message') || 'Value does not match the required format.');
        }
      }
    }

    var equalToRef = el.getAttribute('data-equal-to');
    if (equalToRef) {
      var otherEl = fieldByRef(el, equalToRef);
      if (otherEl && value !== otherEl.value) {
        var bothPassword = type === 'password' && (otherEl.type || '').toLowerCase() === 'password';
        return setInvalid(el, bothPassword ? 'Passwords do not match.' : 'Fields must match.');
      }
    }

    var gteRef = el.getAttribute('data-gte-field');
    if (gteRef) {
      var gteEl = fieldByRef(el, gteRef);
      if (gteEl && gteEl.value !== '' && trimmed !== '') {
        var isNum = type === 'number';
        var a = isNum ? parseFloat(trimmed) : trimmed;
        var b = isNum ? parseFloat(gteEl.value) : gteEl.value;
        if (a < b) {
          if (type === 'date' || type === 'datetime-local') {
            return setInvalid(el, 'End date must be on or after the start date.');
          }
          return setInvalid(el, 'Value must be greater than or equal to the related field.');
        }
      }
    }

    var lteRef = el.getAttribute('data-lte-field');
    if (lteRef) {
      var lteEl = fieldByRef(el, lteRef);
      if (lteEl && lteEl.value !== '' && trimmed !== '') {
        var isNum2 = type === 'number';
        var a2 = isNum2 ? parseFloat(trimmed) : trimmed;
        var b2 = isNum2 ? parseFloat(lteEl.value) : lteEl.value;
        if (a2 > b2) {
          if (type === 'date' || type === 'datetime-local') {
            return setInvalid(el, 'Start date must be on or before the end date.');
          }
          return setInvalid(el, 'Value must be less than or equal to the related field.');
        }
      }
    }

    return setValid(el);
  };

  function validateMinCheckedGroup(container) {
    var min = parseInt(container.getAttribute('data-min-checked'), 10) || 0;
    var boxes = container.querySelectorAll('input[type="checkbox"]');
    var checked = 0;
    for (var i = 0; i < boxes.length; i++) {
      if (boxes[i].checked) checked++;
    }
    var ok = checked >= min;

    var errEl = container.querySelector(':scope > .field-error');
    if (!errEl) {
      errEl = document.createElement('small');
      errEl.className = 'field-error';
      container.appendChild(errEl);
    }
    if (!ok) {
      errEl.textContent = 'Please select at least ' + min + '.';
      errEl.classList.add('is-visible');
      container.classList.add('is-invalid');
    } else {
      errEl.textContent = '';
      errEl.classList.remove('is-visible');
      container.classList.remove('is-invalid');
    }
    return ok;
  }

  /**
   * Validate every control in a form. Returns true if the form can be submitted.
   */
  window.validateForm = function (form) {
    if (!form) return true;
    var controls = form.querySelectorAll('input, select, textarea');
    var firstInvalid = null;
    var allValid = true;

    for (var i = 0; i < controls.length; i++) {
      var el = controls[i];
      if (!isValidatable(el)) continue;
      // Avoid re-validating every radio in a group redundantly beyond need,
      // but it's harmless — just run validateField on each.
      var ok = window.validateField(el);
      if (!ok) {
        allValid = false;
        if (!firstInvalid) firstInvalid = el;
      }
    }

    var groups = form.querySelectorAll('[data-min-checked]');
    for (var g = 0; g < groups.length; g++) {
      var groupOk = validateMinCheckedGroup(groups[g]);
      if (!groupOk) {
        allValid = false;
        if (!firstInvalid) firstInvalid = groups[g];
      }
    }

    if (!allValid && firstInvalid) {
      firstInvalid.focus({ preventScroll: true });
      firstInvalid.scrollIntoView({ block: 'center' });
    }

    return allValid;
  };
})();
