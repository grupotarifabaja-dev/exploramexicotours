/**
 * Beacon de estadísticas propias: registra clicks a WhatsApp, llamadas y
 * solicitudes del cotizador sin frenar la navegación (sendBeacon).
 */
(function () {
  'use strict';
  var url = (window.EMTStats && window.EMTStats.url) || '/wp-admin/admin-ajax.php';

  function send(m) {
    try {
      var d = new FormData();
      d.append('action', 'emt_stats_click');
      d.append('m', m);
      if (navigator.sendBeacon) { navigator.sendBeacon(url, d); }
      else { fetch(url, { method: 'POST', body: d, keepalive: true }); }
    } catch (e) { /* nunca romper la navegación por estadísticas */ }
  }

  document.addEventListener('click', function (e) {
    var el = e.target && e.target.closest ? e.target.closest('a,button') : null;
    if (!el) { return; }
    if (el.hasAttribute('data-cz-enviar')) { send('disp'); return; }
    if (el.hasAttribute('data-cz-info')) { send('info'); return; }
    var href = el.getAttribute ? (el.getAttribute('href') || '') : '';
    if (/wa\.me|api\.whatsapp\.com/.test(href)) { send('whatsapp'); return; }
    if (/^tel:/.test(href)) { send('llamada'); }
  }, true);
})();
