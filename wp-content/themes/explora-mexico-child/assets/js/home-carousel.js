/**
 * Carrusel del home (destinos): scroll horizontal con snap. Flechas en desktop
 * (se muestran solo si el contenido desborda). Sin dependencias.
 */
(function () {
  'use strict';
  var carousels = document.querySelectorAll('[data-carousel]');
  Array.prototype.forEach.call(carousels, function (c) {
    var track = c.querySelector('[data-carousel-track]');
    if (!track) { return; }
    var prev = c.querySelector('[data-carousel-prev]');
    var next = c.querySelector('[data-carousel-next]');

    function step() {
      var card = track.children[0];
      var w = card ? card.getBoundingClientRect().width : track.clientWidth * 0.8;
      var cs = window.getComputedStyle(track);
      var gap = parseFloat(cs.columnGap || cs.gap || '16') || 16;
      return w + gap;
    }
    function update() {
      var max = track.scrollWidth - track.clientWidth;
      c.classList.toggle('is-scrollable', max > 4);
      if (prev) { prev.disabled = track.scrollLeft <= 2; }
      if (next) { next.disabled = track.scrollLeft >= max - 2; }
    }
    if (prev) { prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); }); }
    if (next) { next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); }); }
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    // Imágenes pueden cambiar el ancho al cargar.
    window.addEventListener('load', update);
    update();
  });
})();
