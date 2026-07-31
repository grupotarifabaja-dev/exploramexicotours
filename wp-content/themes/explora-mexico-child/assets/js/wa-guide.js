/**
 * WhatsApp con flujo guiado (doc maestro §9.3).
 * Pasos: intención → destino o nº de personas → email opcional → mensaje
 * pre-llenado a wa.me. Estado en localStorage (clave emt_wa_guide) para no
 * perder el progreso al recargar. El click final en el enlace wa.me lo
 * registra emt-stats.js como "clic a WhatsApp".
 */
(function () {
  'use strict';
  var panel = document.querySelector('[data-wa-guide]');
  var toggle = document.querySelector('[data-wa-guide-toggle]');
  var cfgEl = panel ? panel.querySelector('[data-wa-guide-cfg]') : null;
  if (!panel || !toggle || !cfgEl) { return; }

  var C;
  try { C = JSON.parse(cfgEl.textContent); } catch (e) { return; }
  var T = C.t || {};
  var body = panel.querySelector('[data-wa-guide-body]');
  var KEY = 'emt_wa_guide';

  var state = { step: 'intent', intent: '', destino: '', pax: '', email: '' };
  try {
    var saved = JSON.parse(window.localStorage.getItem(KEY) || 'null');
    if (saved && saved.step) { state = saved; }
  } catch (e) { /* localStorage no disponible: el flujo funciona igual */ }

  function save() {
    try { window.localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {}
  }
  function reset() {
    state = { step: 'intent', intent: '', destino: '', pax: '', email: '' };
    save();
    render();
  }
  function esc(s) {
    var d = document.createElement('i');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
  }

  function buildMsg() {
    var lines = [];
    if (state.intent === 'tour') {
      var l = T.msg_tour + '.';
      if (state.destino) { l = T.msg_tour + ' (' + T.msg_dest + ': ' + state.destino + ').'; }
      lines.push(l);
    } else if (state.intent === 'cotizar') {
      lines.push(T.msg_cot + (state.pax ? ' (' + state.pax + ' ' + T.msg_pax + ')' : '') + '.');
    } else {
      lines.push(T.msg_info);
    }
    if (state.email) { lines.push(T.msg_email + ': ' + state.email); }
    if (C.asesor) { lines.push(T.atendido + ': ' + C.asesor); }
    return lines.join('\n');
  }

  function btn(label, attrs) {
    return '<button type="button" class="emt-wa-guide__opt" ' + (attrs || '') + '>' + esc(label) + '</button>';
  }

  function render() {
    var h = '';
    if (state.step === 'intent') {
      h += '<p class="emt-wa-guide__q">' + esc(T.pregunta) + '</p>';
      h += '<div class="emt-wa-guide__opts">';
      h += btn(T.reservar, 'data-wa-intent="tour"');
      h += btn(T.cotizar, 'data-wa-intent="cotizar"');
      h += btn(T.info, 'data-wa-intent="info"');
      h += '</div>';
    } else if (state.step === 'destino') {
      h += '<p class="emt-wa-guide__q">' + esc(T.q_destino) + '</p>';
      h += '<div class="emt-wa-guide__opts emt-wa-guide__opts--wrap">';
      (C.destinos || []).forEach(function (d) { h += btn(d, 'data-wa-destino="' + esc(d) + '"'); });
      h += btn(T.no_se, 'data-wa-destino=""');
      h += '</div>';
      h += '<button type="button" class="emt-wa-guide__link" data-wa-back="intent">&larr; ' + esc(T.atras) + '</button>';
    } else if (state.step === 'pax') {
      h += '<p class="emt-wa-guide__q">' + esc(T.q_pax) + '</p>';
      h += '<div class="emt-wa-guide__row">';
      h += '<input type="number" class="emt-wa-guide__input" data-wa-pax min="1" max="99" step="1" value="' + esc(state.pax || 2) + '" inputmode="numeric" />';
      h += '<button type="button" class="emt-wa-guide__opt emt-wa-guide__opt--go" data-wa-pax-ok>' + esc(T.continuar) + '</button>';
      h += '</div>';
      h += '<button type="button" class="emt-wa-guide__link" data-wa-back="intent">&larr; ' + esc(T.atras) + '</button>';
    } else if (state.step === 'email') {
      h += '<p class="emt-wa-guide__q">' + esc(T.q_email) + '</p>';
      h += '<div class="emt-wa-guide__row">';
      h += '<input type="email" class="emt-wa-guide__input" data-wa-email placeholder="' + esc(T.email_ph) + '" value="' + esc(state.email) + '" />';
      h += '<button type="button" class="emt-wa-guide__opt emt-wa-guide__opt--go" data-wa-email-ok>' + esc(T.continuar) + '</button>';
      h += '</div>';
      h += '<button type="button" class="emt-wa-guide__link" data-wa-email-skip>' + esc(T.omitir) + '</button>';
    } else { // final
      var msg = buildMsg();
      h += '<p class="emt-wa-guide__q">' + esc(T.listo) + '</p>';
      h += '<div class="emt-wa-guide__msg">' + esc(msg).replace(/\n/g, '<br>') + '</div>';
      h += '<a class="emt-wa-guide__send" href="https://wa.me/' + esc(C.wa) + '?text=' + encodeURIComponent(msg) + '" target="_blank" rel="noopener noreferrer">' + esc(T.abrir) + '</a>';
      h += '<button type="button" class="emt-wa-guide__link" data-wa-restart>' + esc(T.reiniciar) + '</button>';
    }
    body.innerHTML = h;
  }

  function goTo(step) { state.step = step; save(); render(); }

  function open() {
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    render();
  }
  function close() {
    panel.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
  }

  toggle.addEventListener('click', function (e) {
    e.preventDefault(); // sin JS el enlace abre el chat directo; con JS abrimos el asistente
    if (panel.hidden) { open(); } else { close(); }
  });

  panel.addEventListener('click', function (e) {
    var t = e.target.closest('button, a');
    if (!t) { return; }
    if (t.hasAttribute('data-wa-guide-close')) { close(); return; }
    if (t.hasAttribute('data-wa-restart')) { reset(); return; }
    if (t.hasAttribute('data-wa-back')) { goTo(t.getAttribute('data-wa-back')); return; }
    if (t.hasAttribute('data-wa-intent')) {
      state.intent = t.getAttribute('data-wa-intent');
      state.step = state.intent === 'tour' ? 'destino' : (state.intent === 'cotizar' ? 'pax' : 'email');
      save(); render(); return;
    }
    if (t.hasAttribute('data-wa-destino')) {
      state.destino = t.getAttribute('data-wa-destino') || '';
      goTo('email'); return;
    }
    if (t.hasAttribute('data-wa-pax-ok')) {
      var p = parseInt((panel.querySelector('[data-wa-pax]') || {}).value, 10);
      state.pax = (p && p > 0) ? p : 2;
      goTo('email'); return;
    }
    if (t.hasAttribute('data-wa-email-ok')) {
      var em = ((panel.querySelector('[data-wa-email]') || {}).value || '').trim();
      state.email = /.+@.+\..+/.test(em) ? em : '';
      goTo('final'); return;
    }
    if (t.hasAttribute('data-wa-email-skip')) { state.email = ''; goTo('final'); return; }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !panel.hidden) { close(); }
  });
})();
