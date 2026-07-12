/**
 * Hero con video de fondo: carga diferida y responsable.
 * - El <video> sale del HTML SIN fuente (data-src) y con preload="none", así NO
 *   descarga datos por defecto.
 * - Solo en escritorio y sin prefers-reduced-motion se inyecta la fuente y se
 *   reproduce. En móvil / reduced-motion / si el autoplay falla, queda el poster
 *   (imagen de respaldo) — sin gasto de datos ni batería.
 */
(function () {
  'use strict';

  var video = document.querySelector('[data-hero-video]');
  if (!video) { return; }

  var isMobile = window.matchMedia('(max-width: 768px)').matches;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (isMobile || reduced) { return; } // se queda el poster

  var src = video.getAttribute('data-src');
  if (!src) { return; }

  var source = document.createElement('source');
  source.src = src;
  source.type = video.getAttribute('data-type') || 'video/mp4';
  video.appendChild(source);
  video.setAttribute('autoplay', '');
  video.load();

  var p = video.play();
  if (p && typeof p.catch === 'function') {
    // Autoplay bloqueado por el navegador: dejamos el poster visible.
    p.catch(function () {});
  }
})();
