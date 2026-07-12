/**
 * Mega-menú + header sticky + drawer móvil (doc maestro §7.3).
 * Sin dependencias. Accesible: aria-expanded, teclado (Enter/Espacio/Escape).
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var header = document.querySelector('[data-emt-header]');
    if (!header) {
      return;
    }

    /* ---- Sticky: clase al hacer scroll ---- */
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---- Mega-menú ---- */
    var triggers = Array.prototype.slice.call(header.querySelectorAll('[data-mega-trigger]'));
    var panels = Array.prototype.slice.call(header.querySelectorAll('[data-mega-panel]'));
    var openKey = null;

    function panelFor(key) {
      return header.querySelector('[data-mega-panel="' + key + '"]');
    }
    function closeAll() {
      triggers.forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
      panels.forEach(function (p) { p.classList.remove('is-open'); });
      openKey = null;
    }
    function open(key) {
      if (openKey === key) { return; }
      closeAll();
      var trigger = header.querySelector('[data-mega-trigger="' + key + '"]');
      var panel = panelFor(key);
      if (trigger && panel) {
        trigger.setAttribute('aria-expanded', 'true');
        panel.classList.add('is-open');
        openKey = key;
      }
    }

    var isDesktop = function () { return window.matchMedia('(min-width: 720px)').matches; };

    // Cierre con retardo cancelable: evita que el menú se cierre al cruzar el
    // hueco entre el disparador y el panel (clásico bug del mega-menú).
    var closeTimer = null;
    function cancelClose() { if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; } }
    function scheduleClose() { cancelClose(); closeTimer = setTimeout(closeAll, 250); }

    triggers.forEach(function (trigger) {
      var key = trigger.getAttribute('data-mega-trigger');
      var item = trigger.closest('.emt-nav__item');

      // Click / tap: alterna (sirve para desktop y como fallback en móvil)
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        if (openKey === key) { closeAll(); } else { open(key); }
      });

      // Hover en desktop
      if (item) {
        item.addEventListener('mouseenter', function () { if (isDesktop()) { cancelClose(); open(key); } });
        item.addEventListener('mouseleave', function () { if (isDesktop()) { scheduleClose(); } });
      }
    });

    // Mantener abierto al pasar el cursor del disparador al panel; cerrar al salir de él.
    panels.forEach(function (panel) {
      panel.addEventListener('mouseenter', function () { if (isDesktop()) { cancelClose(); } });
      panel.addEventListener('mouseleave', function () { if (isDesktop()) { scheduleClose(); } });
    });

    // Cerrar con Escape y al hacer click fuera
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && openKey) { closeAll(); }
    });
    document.addEventListener('click', function (e) {
      if (openKey && !header.contains(e.target)) { closeAll(); }
    });

    /* ---- Drawer móvil / hamburguesa ---- */
    var burger = header.querySelector('[data-emt-burger]');
    var drawer = header.querySelector('[data-emt-drawer]');
    var overlay = header.querySelector('[data-emt-drawer-overlay]');

    function setDrawer(openIt) {
      if (!burger || !drawer) { return; }
      burger.setAttribute('aria-expanded', openIt ? 'true' : 'false');
      drawer.classList.toggle('is-open', openIt);
      if (overlay) {
        overlay.hidden = !openIt;
        overlay.classList.toggle('is-open', openIt);
      }
      document.documentElement.style.overflow = openIt ? 'hidden' : '';
    }

    if (burger && drawer) {
      drawer.hidden = false; // visibilidad real por transform/.is-open
      burger.addEventListener('click', function () {
        setDrawer(burger.getAttribute('aria-expanded') !== 'true');
      });
      if (overlay) {
        overlay.addEventListener('click', function () { setDrawer(false); });
      }
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { setDrawer(false); }
      });
    }
  });
})();
