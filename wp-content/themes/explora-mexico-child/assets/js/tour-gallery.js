/**
 * Galería de la ficha de tour: lightbox accesible sin dependencias.
 * - Abre al hacer clic en la foto destacada o en una miniatura.
 * - Navegación con flechas (botones, teclado ← →) y swipe en táctil.
 * - Cierra con la X, clic en el fondo o tecla Escape.
 * - Gestiona foco y atributos aria; bloquea el scroll de fondo.
 */
(function () {
  'use strict';

  var gallery = document.querySelector('[data-gallery]');
  var box = document.querySelector('[data-lightbox]');
  if (!gallery || !box) { return; }

  var dataEl = gallery.querySelector('[data-gallery-data]');
  var images = [];
  try { images = JSON.parse(dataEl ? dataEl.textContent : '[]') || []; } catch (e) { images = []; }
  if (!images.length) { return; }

  var imgEl = box.querySelector('[data-lb-img]');
  var counterEl = box.querySelector('[data-lb-counter]');
  var btnPrev = box.querySelector('[data-lb-prev]');
  var btnNext = box.querySelector('[data-lb-next]');
  var btnClose = box.querySelector('[data-lb-close]');

  var current = 0;
  var lastFocused = null;
  var single = images.length < 2;

  // Con una sola imagen no tiene sentido la navegación.
  if (single) {
    if (btnPrev) { btnPrev.hidden = true; }
    if (btnNext) { btnNext.hidden = true; }
    if (counterEl) { counterEl.hidden = true; }
  }

  function render() {
    var item = images[current];
    if (!item) { return; }
    imgEl.src = item.src;
    imgEl.alt = item.alt || '';
    if (counterEl && !single) { counterEl.textContent = (current + 1) + ' / ' + images.length; }
  }

  function open(index) {
    current = (index + images.length) % images.length;
    lastFocused = document.activeElement;
    render();
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    // Foco al botón de cerrar para navegación por teclado.
    if (btnClose) { btnClose.focus(); }
    document.addEventListener('keydown', onKey);
  }

  function close() {
    box.hidden = true;
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKey);
    imgEl.src = '';
    if (lastFocused && typeof lastFocused.focus === 'function') { lastFocused.focus(); }
  }

  function go(step) {
    if (single) { return; }
    current = (current + step + images.length) % images.length;
    render();
  }

  function onKey(e) {
    switch (e.key) {
      case 'Escape': close(); break;
      case 'ArrowRight': go(1); break;
      case 'ArrowLeft': go(-1); break;
      case 'Tab':
        // Trampa de foco simple dentro del modal.
        var focusables = box.querySelectorAll('button:not([hidden])');
        if (!focusables.length) { break; }
        var first = focusables[0];
        var last = focusables[focusables.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        break;
    }
  }

  // Disparadores de apertura (foto destacada + miniaturas).
  gallery.querySelectorAll('[data-gallery-open]').forEach(function (el) {
    el.addEventListener('click', function () {
      open(parseInt(el.getAttribute('data-gallery-open'), 10) || 0);
    });
  });

  if (btnClose) { btnClose.addEventListener('click', close); }
  if (btnPrev) { btnPrev.addEventListener('click', function () { go(-1); }); }
  if (btnNext) { btnNext.addEventListener('click', function () { go(1); }); }

  // Clic en el fondo (no en la imagen ni en los controles) cierra.
  box.addEventListener('click', function (e) {
    if (e.target === box || e.target.classList.contains('emt-lightbox__stage')) { close(); }
  });

  // Swipe en táctil.
  var touchX = null;
  box.addEventListener('touchstart', function (e) { touchX = e.changedTouches[0].clientX; }, { passive: true });
  box.addEventListener('touchend', function (e) {
    if (touchX === null) { return; }
    var dx = e.changedTouches[0].clientX - touchX;
    if (Math.abs(dx) > 40) { go(dx < 0 ? 1 : -1); }
    touchX = null;
  }, { passive: true });
})();
