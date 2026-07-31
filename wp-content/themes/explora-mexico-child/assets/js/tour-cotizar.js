/**
 * Cotizador del tour (tarjeta de reserva).
 * Modelos de cálculo, en orden de prioridad:
 *  1. Por vehículo (tramos con precio p/p según total de pasajeros).
 *  2. Por ocupación (2–4 adultos por habitación; tarifa de menor si existe).
 *  3. Solo "precio desde" -> total estimado desde.
 *  4. Sin precios -> sin total; solo fecha + pax y solicitud por WhatsApp.
 * El mensaje se envía a WhatsApp con la atribución del asesor (cookie ?ref).
 */
(function () {
  'use strict';
  var root = document.querySelector('[data-cotizador]');
  if (!root) { return; }
  var dataEl = root.querySelector('[data-cotizador-data]');
  if (!dataEl) { return; }
  var D;
  try { D = JSON.parse(dataEl.textContent); } catch (e) { return; }

  var toggle = root.querySelector('[data-cz-toggle]');
  var form = root.querySelector('[data-cz-form]');
  var fFecha = root.querySelector('[data-cz-fecha]');
  var fAd = root.querySelector('[data-cz-adultos]');
  var fMe = root.querySelector('[data-cz-menores]');
  var elTotal = root.querySelector('[data-cz-total]');
  var elNota = root.querySelector('[data-cz-nota]');
  var btnEnviar = root.querySelector('[data-cz-enviar]');
  var btnInfo = document.querySelector('[data-cz-info]');

  function fmt(n) {
    return '$' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' MXN';
  }
  function pax() {
    var a = Math.max(1, parseInt(fAd && fAd.value, 10) || 1);
    var m = fMe ? Math.max(0, parseInt(fMe.value, 10) || 0) : 0;
    return { a: a, m: m };
  }

  /* Devuelve {total, estimado, nota} o null si no hay cálculo posible. */
  function calc() {
    var p = pax();
    // 1. Vehículo
    if (D.veh && D.veh.length) {
      var n = p.a + p.m;
      for (var i = 0; i < D.veh.length; i++) {
        if (n >= D.veh[i].min && n <= D.veh[i].max) {
          return { total: n * D.veh[i].pp, estimado: false, pp: D.veh[i].pp, nota: '' };
        }
      }
      return { total: null, nota: D.t.grupo };
    }
    // 2. Ocupación
    var keys = Object.keys(D.ocup || {});
    if (keys.length) {
      var rate = D.ocup[String(p.a)];
      if (p.a >= 2 && p.a <= 4 && rate) {
        var total = p.a * rate;
        var nota = '';
        if (p.m > 0) {
          if (D.menor) { total += p.m * D.menor; }
          else { nota = '+ ' + p.m + ' ' + D.t.menores_cot; }
        }
        return { total: total, estimado: false, nota: nota };
      }
      return { total: null, nota: D.t.grupo };
    }
    // 3. Solo desde
    if (D.desde) {
      return { total: (p.a + p.m) * D.desde, estimado: true, nota: '' };
    }
    return null;
  }

  function refresh() {
    if (!elTotal) { return; }
    var r = calc();
    if (!r) { elTotal.hidden = true; elNota.hidden = true; return; }
    if (r.total !== null) {
      var lbl = r.estimado ? D.t.estimado_desde : D.t.total;
      var pp = r.pp ? ' (' + fmt(r.pp).replace(' MXN', '') + ' ' + D.t.pp + ')' : '';
      elTotal.textContent = lbl + ': ' + fmt(r.total) + pp;
      elTotal.hidden = false;
    } else {
      elTotal.hidden = true;
    }
    if (r.nota) { elNota.textContent = r.nota; elNota.hidden = false; }
    else { elNota.hidden = true; }
  }

  function waSend(lines) {
    var msg = lines.filter(Boolean).join('\n');
    window.open('https://wa.me/' + D.wa + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
  }

  if (toggle && form) {
    toggle.addEventListener('click', function () {
      var open = form.hidden;
      form.hidden = !open;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) { refresh(); }
    });
  }
  [fFecha, fAd, fMe].forEach(function (el) {
    if (el) { el.addEventListener('input', refresh); el.addEventListener('change', refresh); }
  });

  if (btnEnviar) {
    btnEnviar.addEventListener('click', function () {
      var p = pax();
      var r = calc();
      var lines = [
        D.t.wa_disp + ' *' + D.titulo + '*.',
        (fFecha && fFecha.value) ? (D.t.fecha + ': ' + fFecha.value) : '',
        D.t.adultos + ': ' + p.a + (fMe ? (' · ' + D.t.menores + ': ' + p.m) : ''),
      ];
      if (r && r.total !== null) {
        lines.push((r.estimado ? D.t.estimado_desde : D.t.total) + ': ' + fmt(r.total) + (r.nota ? ' (' + r.nota + ')' : ''));
      } else if (r && r.nota) {
        lines.push(r.nota);
      }
      if (D.asesor) { lines.push(D.t.atendido + ': ' + D.asesor); }
      waSend(lines);
    });
  }

  if (btnInfo) {
    btnInfo.addEventListener('click', function () {
      var lines = [D.t.wa_info + ' *' + D.titulo + '*.'];
      if (D.asesor) { lines.push(D.t.atendido + ': ' + D.asesor); }
      waSend(lines);
    });
  }
})();
