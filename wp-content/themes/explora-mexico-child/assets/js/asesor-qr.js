/**
 * QR de la vCard del asesor (doc maestro §8.5).
 * El contenedor [data-vcard-url] lleva la URL de la vCard (que el QR codifica).
 *
 * Renderiza el QR si hay una librería disponible (window.QRCode, p. ej. una
 * lib self-hosted que se decida bundlear). Si no, muestra un enlace de
 * respaldo a la vCard para no dejar el contenedor vacío.
 *
 * TODO (decisión pendiente): elegir la librería de QR a self-hostear vs.
 * generar el QR server-side; ambas opciones evitan dependencias externas.
 */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var box = document.querySelector('.emt-asesor-qr[data-vcard-url]');
    if (!box) {
      return;
    }
    var url = box.getAttribute('data-vcard-url');

    if (window.QRCode && typeof window.QRCode === 'function') {
      try {
        box.textContent = '';
        new window.QRCode(box, { text: url, width: 132, height: 132 });
        return;
      } catch (e) { /* cae al respaldo */ }
    }

    // Respaldo: enlace a la vCard mientras no haya librería de QR.
    if (!box.querySelector('a')) {
      var a = document.createElement('a');
      a.href = url;
      a.textContent = 'vCard';
      a.className = 'emt-asesor-qr__fallback';
      box.appendChild(a);
    }
  });
})();
