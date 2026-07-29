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

/* Conteo animado de la banda de cifras (una vez, al entrar en viewport). */
(function () {
  'use strict';
  var nums = document.querySelectorAll('[data-emt-count]');
  if (!nums.length || !('IntersectionObserver' in window)) { return; }
  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) { return; }
      io.unobserve(en.target);
      var el = en.target;
      var target = parseInt(el.getAttribute('data-emt-count'), 10) || 0;
      var suffix = el.getAttribute('data-emt-suffix') || '';
      if (reduced) { el.textContent = target + suffix; return; }
      var start = null, dur = 1200;
      function step(ts) {
        if (!start) { start = ts; }
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased) + suffix;
        if (p < 1) { requestAnimationFrame(step); }
      }
      requestAnimationFrame(step);
    });
  }, { threshold: 0.4 });
  nums.forEach(function (el) { io.observe(el); });
})();
