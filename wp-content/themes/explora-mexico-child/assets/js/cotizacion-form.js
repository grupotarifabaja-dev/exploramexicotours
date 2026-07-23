/**
 * Cotización de grupos: envío del formulario por AJAX (admin-ajax + nonce),
 * con validación ligera que no pierde lo capturado. Mensajes bilingües vía data-*.
 */
(function () {
  'use strict';
  var form = document.querySelector('[data-emt-cotiza-form]');
  if (!form) { return; }

  var msg = form.querySelector('[data-cotiza-msg]');
  var btn = form.querySelector('button[type="submit"]');
  var ajaxUrl = form.getAttribute('data-ajax');
  var nonce = form.getAttribute('data-nonce');
  var msgError = form.getAttribute('data-msg-error') || 'Revisa los campos obligatorios.';
  var msgConn = form.getAttribute('data-msg-conexion') || 'Error de conexión.';
  var msgSending = form.getAttribute('data-msg-enviando') || 'Enviando…';

  function setMsg(text, cls) {
    if (!msg) { return; }
    msg.textContent = text || '';
    msg.className = 'emt-cotiza-form__msg' + (cls ? ' ' + cls : '');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    form.querySelectorAll('.emt-field--error').forEach(function (el) { el.classList.remove('emt-field--error'); });

    var required = ['nombre', 'telefono', 'correo', 'personas'];
    var firstErr = null;
    required.forEach(function (name) {
      var f = form.querySelector('[name="' + name + '"]');
      if (f && (!f.value || !f.value.trim())) {
        var field = f.closest('.emt-field');
        if (field) { field.classList.add('emt-field--error'); }
        if (!firstErr) { firstErr = f; }
      }
    });
    if (firstErr) {
      setMsg(msgError, 'is-err');
      firstErr.focus();
      return;
    }

    var data = new FormData(form);
    data.append('action', 'emt_cotizacion');
    data.append('nonce', nonce);

    if (btn) { btn.disabled = true; }
    setMsg(msgSending, '');

    fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) {
          setMsg((res.data && res.data.msg) || '¡Solicitud recibida!', 'is-ok');
          form.reset();
        } else {
          setMsg((res && res.data && res.data.msg) || msgError, 'is-err');
        }
      })
      .catch(function () { setMsg(msgConn, 'is-err'); })
      .finally(function () { if (btn) { btn.disabled = false; } });
  });
})();
