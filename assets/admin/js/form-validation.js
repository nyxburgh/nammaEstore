/**
 * Shared client-side validation engine for the admin panel.
 *
 * Inline attributes wire fields to these globals (the one allowed inline-JS
 * pattern in this codebase — see CLAUDE.md):
 *   oninput="validateField(this)" onblur="validateField(this)"
 *   onsubmit="return validateForm(this)"
 *
 * Rules never clear or reset a field's value — only .is-invalid /
 * .invalid-feedback are toggled.
 */
(function () {
  'use strict';

  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  var PHONE_RE = /^[0-9+\-\s]{7,15}$/;
  var PHONE_DIGITS_MIN = 7;
  var PHONE_DIGITS_MAX = 15;

  function getFeedback(el) {
    var next = el.nextElementSibling;
    if (next && next.classList && next.classList.contains('invalid-feedback')) {
      return next;
    }
    var div = document.createElement('div');
    div.className = 'invalid-feedback';
    el.insertAdjacentElement('afterend', div);
    return div;
  }

  function setInvalid(el, message) {
    el.classList.add('is-invalid');
    el.setAttribute('aria-invalid', 'true');
    getFeedback(el).textContent = message;
  }

  function setValid(el) {
    el.classList.remove('is-invalid');
    el.setAttribute('aria-invalid', 'false');
    var next = el.nextElementSibling;
    if (next && next.classList && next.classList.contains('invalid-feedback')) {
      next.textContent = '';
    }
  }

  function isBlank(v) {
    return v === undefined || v === null || String(v).trim() === '';
  }

  function radioGroupChecked(name, form) {
    var radios = form.querySelectorAll('input[type="radio"][name="' + cssEscape(name) + '"]');
    for (var i = 0; i < radios.length; i++) {
      if (radios[i].checked) return true;
    }
    return false;
  }

  function cssEscape(s) {
    return String(s).replace(/([^\w-])/g, '\\$1');
  }

  function countDigits(v) {
    return (String(v).match(/\d/g) || []).length;
  }

  function compareValues(a, b, isDateType) {
    if (isDateType) {
      var da = new Date(a).getTime();
      var db = new Date(b).getTime();
      if (isNaN(da) || isNaN(db)) return null;
      return da === db ? 0 : (da < db ? -1 : 1);
    }
    var na = parseFloat(a), nb = parseFloat(b);
    if (isNaN(na) || isNaN(nb)) return null;
    return na === nb ? 0 : (na < nb ? -1 : 1);
  }

  /**
   * Validate a single form control. Returns true/false and updates the
   * Bootstrap .is-invalid / .invalid-feedback UI. Never mutates el.value.
   */
  function validateField(el) {
    if (!el || el.disabled) return true;

    var tag = el.tagName.toLowerCase();
    var type = (el.getAttribute('type') || (tag === 'select' ? 'select' : 'text')).toLowerCase();

    if (type === 'submit' || type === 'button' || type === 'reset' || type === 'hidden') {
      return true;
    }

    var form = el.form;
    var required = el.hasAttribute('required');
    var value = el.value;

    // required checks
    if (type === 'checkbox') {
      if (required && !el.checked) {
        setInvalid(el, 'This field is required.');
        return false;
      }
      setValid(el);
      return true;
    }

    if (type === 'radio') {
      if (required && form && !radioGroupChecked(el.name, form)) {
        setInvalid(el, 'Please select an option.');
        return false;
      }
      setValid(el);
      return true;
    }

    if (type === 'file') {
      if (required && el.files && el.files.length === 0) {
        setInvalid(el, 'Please choose a file.');
        return false;
      }
      setValid(el);
      return true;
    }

    if (tag === 'select') {
      if (required && isBlank(value)) {
        setInvalid(el, 'Please make a selection.');
        return false;
      }
      setValid(el);
      return true;
    }

    // text-like inputs / textarea
    if (required && isBlank(value)) {
      setInvalid(el, 'This field is required.');
      return false;
    }

    // Everything below only applies when a value is actually present.
    if (!isBlank(value)) {
      if (type === 'email' && !EMAIL_RE.test(value)) {
        setInvalid(el, 'Enter a valid email address.');
        return false;
      }

      if (type === 'tel') {
        var pattern = el.getAttribute('pattern');
        if (pattern) {
          var re;
          try { re = new RegExp('^(?:' + pattern + ')$'); } catch (e) { re = null; }
          if (re && !re.test(value)) {
            setInvalid(el, 'Enter a valid phone number.');
            return false;
          }
        } else {
          var digits = countDigits(value);
          if (!PHONE_RE.test(value) || digits < PHONE_DIGITS_MIN || digits > PHONE_DIGITS_MAX) {
            setInvalid(el, 'Enter a valid phone number (7–15 digits).');
            return false;
          }
        }
      }

      if (type === 'url') {
        try {
          new URL(value);
        } catch (e) {
          setInvalid(el, 'Enter a valid URL.');
          return false;
        }
      }

      if (type === 'number') {
        var num = parseFloat(value);
        if (isNaN(num)) {
          setInvalid(el, 'Enter a valid number.');
          return false;
        }
        var min = el.getAttribute('min');
        var max = el.getAttribute('max');
        if (min !== null && min !== '' && num < parseFloat(min)) {
          setInvalid(el, 'Value must be at least ' + min + '.');
          return false;
        }
        if (max !== null && max !== '' && num > parseFloat(max)) {
          setInvalid(el, 'Value must be at most ' + max + '.');
          return false;
        }
      }

      if (type === 'date' || type === 'datetime-local') {
        var min2 = el.getAttribute('min');
        var max2 = el.getAttribute('max');
        var cmp;
        if (min2) {
          cmp = compareValues(value, min2, true);
          if (cmp !== null && cmp < 0) {
            setInvalid(el, 'Date must be on or after ' + min2 + '.');
            return false;
          }
        }
        if (max2) {
          cmp = compareValues(value, max2, true);
          if (cmp !== null && cmp > 0) {
            setInvalid(el, 'Date must be on or before ' + max2 + '.');
            return false;
          }
        }
      }

      var minlength = el.getAttribute('minlength');
      if (minlength !== null && value.length < parseInt(minlength, 10)) {
        setInvalid(el, 'Must be at least ' + minlength + ' characters.');
        return false;
      }

      var maxlength = el.getAttribute('maxlength');
      if (maxlength !== null && value.length > parseInt(maxlength, 10)) {
        setInvalid(el, 'Must be at most ' + maxlength + ' characters.');
        return false;
      }

      var nativePattern = el.getAttribute('pattern');
      if (nativePattern && type !== 'tel') {
        var nre;
        try { nre = new RegExp('^(?:' + nativePattern + ')$'); } catch (e2) { nre = null; }
        if (nre && !nre.test(value)) {
          setInvalid(el, 'Value does not match the required format.');
          return false;
        }
      }

      var dataPattern = el.getAttribute('data-pattern');
      if (dataPattern) {
        var dre;
        try { dre = new RegExp(dataPattern); } catch (e3) { dre = null; }
        if (dre && !dre.test(value)) {
          setInvalid(el, el.getAttribute('data-pattern-message') || 'Value does not match the required format.');
          return false;
        }
      }

      var equalTo = el.getAttribute('data-equal-to');
      if (equalTo) {
        var other = document.querySelector(equalTo);
        if (other && value !== other.value) {
          setInvalid(el, 'Values do not match.');
          return false;
        }
      }

      var gteSel = el.getAttribute('data-gte-field');
      if (gteSel) {
        var gteEl = document.querySelector(gteSel);
        if (gteEl && !isBlank(gteEl.value)) {
          var isDate = (type === 'date' || type === 'datetime-local');
          var c = compareValues(value, gteEl.value, isDate);
          if (c !== null && c < 0) {
            setInvalid(el, 'Must be the same as or after ' + (gteEl.previousElementSibling && gteEl.previousElementSibling.textContent ? gteEl.previousElementSibling.textContent.replace('*', '').trim() : 'the start value') + '.');
            return false;
          }
        }
      }

      var lteSel = el.getAttribute('data-lte-field');
      if (lteSel) {
        var lteEl = document.querySelector(lteSel);
        if (lteEl && !isBlank(lteEl.value)) {
          var isDate2 = (type === 'date' || type === 'datetime-local');
          var c2 = compareValues(value, lteEl.value, isDate2);
          if (c2 !== null && c2 > 0) {
            setInvalid(el, 'Must not exceed ' + (lteEl.previousElementSibling && lteEl.previousElementSibling.textContent ? lteEl.previousElementSibling.textContent.replace('*', '').trim() : 'the reference value') + '.');
            return false;
          }
        }
      }
    }

    setValid(el);
    return true;
  }

  function validateCheckboxGroup(container) {
    var min = parseInt(container.getAttribute('data-min-checked'), 10) || 1;
    var checked = container.querySelectorAll('input[type="checkbox"]:checked').length;
    var ok = checked >= min;

    var msgEl = container.querySelector('.checkbox-group-feedback');
    if (!msgEl) {
      msgEl = document.createElement('div');
      msgEl.className = 'text-danger small mt-1 checkbox-group-feedback';
      container.appendChild(msgEl);
    }

    if (ok) {
      msgEl.textContent = '';
    } else {
      msgEl.textContent = min === 1 ? 'Select at least one option.' : 'Select at least ' + min + ' options.';
    }
    return ok;
  }

  /**
   * Validate every control in the form. Focuses and scrolls to the first
   * invalid field. Returns true only if everything passes.
   */
  function validateForm(form) {
    var controls = form.querySelectorAll('input, select, textarea');
    var firstInvalid = null;
    var seenRadioGroups = {};

    controls.forEach(function (el) {
      if (el.disabled) return;
      var type = (el.getAttribute('type') || '').toLowerCase();
      if (type === 'submit' || type === 'button' || type === 'reset' || type === 'hidden') return;

      if (type === 'radio') {
        if (seenRadioGroups[el.name]) return;
        seenRadioGroups[el.name] = true;
      }

      var ok = validateField(el);
      if (!ok && !firstInvalid) firstInvalid = el;
    });

    var groups = form.querySelectorAll('[data-min-checked]');
    groups.forEach(function (container) {
      var ok = validateCheckboxGroup(container);
      if (!ok && !firstInvalid) {
        firstInvalid = container.querySelector('input[type="checkbox"]') || container;
      }
    });

    if (firstInvalid) {
      if (typeof firstInvalid.focus === 'function') firstInvalid.focus();
      if (typeof firstInvalid.scrollIntoView === 'function') {
        firstInvalid.scrollIntoView({ block: 'center' });
      }
      return false;
    }

    return true;
  }

  window.validateField = validateField;
  window.validateForm = validateForm;
})();
