/**
 * Explora Transfer: modal de cotización + envío del formulario.
 *
 * - El formulario vive dentro de un <dialog class="emt-modal">. Se abre desde el
 *   CTA del hero, el CTA de contacto y el botón "Cotizar" de cada vehículo de la
 *   flotilla (que además precarga el vehículo de interés).
 * - Envío AJAX (admin-ajax + nonce) con validación ligera que no pierde lo
 *   capturado; mensajes bilingües inyectados desde el template vía data-*.
 */
(function () {
  'use strict';

  var form = document.querySelector('[data-emt-transfer-form]');
  if (!form) { return; }

  var modal = document.querySelector('[data-emt-transfer-modal]');
  var msg = form.querySelector('[data-transfer-msg]');
  var btn = form.querySelector('button[type="submit"]');
  var ajaxUrl = form.getAttribute('data-ajax');
  var nonce = form.getAttribute('data-nonce');

  var vehiculoInput = form.querySelector('[data-transfer-vehiculo-input]');
  var vehiculoWrap = modal ? modal.querySelector('[data-modal-vehiculo]') : null;
  var vehiculoName = modal ? modal.querySelector('[data-modal-vehiculo-name]') : null;
  var lastOpener = null;

  /* ---------- Modal ---------- */
  function lockScroll(on) {
    document.documentElement.classList.toggle('emt-modal-open', !!on);
  }

  function setVehiculo(name) {
    if (vehiculoInput) { vehiculoInput.value = name || ''; }
    if (vehiculoWrap && vehiculoName) {
      if (name) {
        vehiculoName.textContent = name;
        vehiculoWrap.hidden = false;
      } else {
        vehiculoName.textContent = '';
        vehiculoWrap.hidden = true;
      }
    }
  }

  function openModal(vehiculo, opener) {
    if (!modal) { return; }
    lastOpener = opener || null;
    setMsg('', null);
    setVehiculo(vehiculo);
    if (typeof modal.showModal === 'function') {
      if (!modal.open) { modal.showModal(); }
    } else {
      modal.setAttribute('open', ''); // fallback navegadores sin <dialog>
    }
    lockScroll(true);
    // Foco al primer campo para accesibilidad.
    var first = form.querySelector('input, select, textarea');
    if (first) { try { first.focus(); } catch (e) {} }
  }

  function closeModal() {
    if (!modal) { return; }
    if (typeof modal.close === 'function' && modal.open) {
      modal.close();
    } else {
      modal.removeAttribute('open');
    }
    lockScroll(false);
    if (lastOpener && typeof lastOpener.focus === 'function') {
      try { lastOpener.focus(); } catch (e) {}
    }
  }

  // Disparadores de apertura (hero, contacto y tarjetas de flotilla).
  document.querySelectorAll('[data-emt-transfer-open]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      openModal(el.getAttribute('data-vehiculo') || '', el);
    });
  });

  if (modal) {
    // Botón cerrar.
    modal.querySelectorAll('[data-emt-transfer-close]').forEach(function (el) {
      el.addEventListener('click', function () { closeModal(); });
    });
    // Clic en el backdrop (fuera del panel) cierra.
    modal.addEventListener('click', function (e) {
      if (e.target === modal) { closeModal(); }
    });
    // ESC / cierre nativo del <dialog>: restaura el scroll.
    modal.addEventListener('cancel', function (e) {
      e.preventDefault();
      closeModal();
    });
    modal.addEventListener('close', function () { lockScroll(false); });
  }

  /* ---------- Envío ---------- */
  function setMsg(text, ok) {
    if (!msg) { return; }
    msg.textContent = text;
    msg.className = 'emt-transfer-form__msg' + (ok === true ? ' is-ok' : (ok === false ? ' is-err' : ''));
  }


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

    waAbrir();

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
          setVehiculo('');
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
