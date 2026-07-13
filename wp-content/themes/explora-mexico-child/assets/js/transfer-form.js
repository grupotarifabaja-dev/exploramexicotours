/**
 * Formulario de reservación de Explora Transfer.
 * Envío AJAX (admin-ajax + nonce) con validación ligera que no pierde lo
 * capturado; mensajes bilingües inyectados desde el template vía data-*.
 */
(function () {
  'use strict';

  var form = document.querySelector('[data-emt-transfer-form]');
  if (!form) { return; }

  var msg = form.querySelector('[data-transfer-msg]');
  var btn = form.querySelector('button[type="submit"]');
  var ajaxUrl = form.getAttribute('data-ajax');
  var nonce = form.getAttribute('data-nonce');

  function setMsg(text, ok) {
    if (!msg) { return; }
    msg.textContent = text;
    msg.className = 'emt-transfer-form__msg' + (ok === true ? ' is-ok' : (ok === false ? ' is-err' : ''));
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Validación de obligatorios sin resetear nada.
    var firstErr = null;
    form.querySelectorAll('[required]').forEach(function (f) {
      f.closest('.emt-field').classList.remove('emt-field--error');
      var empty = !f.value || !f.value.trim();
      var badEmail = f.type === 'email' && f.value && f.value.indexOf('@') === -1;
      if (empty || badEmail) {
        f.closest('.emt-field').classList.add('emt-field--error');
        if (!firstErr) { firstErr = f; }
      }
    });
    if (firstErr) {
      setMsg(form.getAttribute('data-msg-error'), false);
      firstErr.focus();
      return;
    }

    var data = new FormData(form);
    data.append('action', 'emt_transfer_solicitud');
    data.append('nonce', nonce);

    setMsg(form.getAttribute('data-msg-enviando'), null);
    if (btn) { btn.disabled = true; }

    fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) {
          setMsg(res.data.msg, true);
          form.reset();
        } else {
          setMsg((res && res.data && res.data.msg) || form.getAttribute('data-msg-error'), false);
        }
      })
      .catch(function () {
        setMsg(form.getAttribute('data-msg-conexion'), false);
      })
      .then(function () {
        if (btn) { btn.disabled = false; }
      });
  });
})();
