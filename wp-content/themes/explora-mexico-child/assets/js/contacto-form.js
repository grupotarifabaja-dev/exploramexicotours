/** Contacto: envío del formulario por AJAX (admin-ajax + nonce), validación ligera. */
(function () {
  'use strict';
  var form = document.querySelector('[data-emt-contacto-form]');
  if (!form) { return; }
  var msg = form.querySelector('[data-contacto-msg]');
  var btn = form.querySelector('button[type="submit"]');
  var ajaxUrl = form.getAttribute('data-ajax');
  var nonce = form.getAttribute('data-nonce');
  var msgError = form.getAttribute('data-msg-error') || 'Revisa los campos obligatorios.';
  var msgConn = form.getAttribute('data-msg-conexion') || 'Error de conexión.';
  var msgSending = form.getAttribute('data-msg-enviando') || 'Enviando…';

  function setMsg(t, c) { if (!msg) { return; } msg.textContent = t || ''; msg.className = 'emt-contacto-form__msg' + (c ? ' ' + c : ''); }


  /* WhatsApp: por ahora la solicitud también se envía por WhatsApp con los
     datos capturados (y la atribución del asesor si hay cookie ?ref). */
  function waAbrir() {
    var num = form.getAttribute('data-wa');
    if (!num) { return; }
    var lines = [form.getAttribute('data-wa-titulo') || ''];
    form.querySelectorAll('input, select, textarea').forEach(function (f) {
      if (!f.name || f.name === 'action' || f.name === 'nonce') { return; }
      if (f.type === 'submit' || f.type === 'button') { return; }
      var val = '';
      if (f.tagName === 'SELECT') {
        var op = f.options[f.selectedIndex];
        val = (op && op.value) ? op.textContent.trim() : '';
      } else if (f.type === 'checkbox' || f.type === 'radio') {
        if (!f.checked) { return; }
        val = f.value;
      } else {
        val = (f.value || '').trim();
      }
      if (!val) { return; }
      var lbl = '';
      var wrap = f.closest('.emt-field');
      if (wrap) {
        var lab = wrap.querySelector('label');
        if (lab) { lbl = lab.textContent.replace(/\s*\*\s*$/, '').trim(); }
      }
      lines.push((lbl || f.name.replace(/_/g, ' ')) + ': ' + val);
    });
    var quien = form.getAttribute('data-wa-atendido');
    if (quien) { lines.push((form.getAttribute('data-wa-atendido-label') || 'Atendido por') + ': ' + quien); }
    window.open('https://wa.me/' + num + '?text=' + encodeURIComponent(lines.filter(Boolean).join('\n')), '_blank', 'noopener');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    form.querySelectorAll('.emt-field--error').forEach(function (el) { el.classList.remove('emt-field--error'); });
    var firstErr = null;
    ['nombre', 'correo', 'mensaje'].forEach(function (name) {
      var f = form.querySelector('[name="' + name + '"]');
      if (f && (!f.value || !f.value.trim())) {
        var field = f.closest('.emt-field'); if (field) { field.classList.add('emt-field--error'); }
        if (!firstErr) { firstErr = f; }
      }
    });
    if (firstErr) { setMsg(msgError, 'is-err'); firstErr.focus(); return; }

    waAbrir();

    var data = new FormData(form);
    data.append('action', 'emt_contacto');
    data.append('nonce', nonce);
    if (btn) { btn.disabled = true; }
    setMsg(msgSending, '');

    fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) { setMsg((res.data && res.data.msg) || '¡Mensaje enviado!', 'is-ok'); form.reset(); }
        else { setMsg((res && res.data && res.data.msg) || msgError, 'is-err'); }
      })
      .catch(function () { setMsg(msgConn, 'is-err'); })
      .finally(function () { if (btn) { btn.disabled = false; } });
  });
})();
