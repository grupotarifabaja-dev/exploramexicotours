/**
 * Buscador del hero: autocompletado con diseño propio (sustituye al datalist
 * nativo). Filtra sin acentos, muestra máx. 8 sugerencias con su tipo,
 * navegable con teclado (↑ ↓ Enter Esc) y clic.
 */
(function () {
  'use strict';
  var form = document.querySelector('[data-hero-search]');
  if (!form) { return; }
  var input = form.querySelector('[data-hero-search-input]');
  var panel = form.querySelector('[data-hero-search-sug]');
  var dataEl = form.querySelector('[data-hero-search-data]');
  if (!input || !panel || !dataEl) { return; }

  var SUG;
  try { SUG = JSON.parse(dataEl.textContent) || []; } catch (e) { return; }

  function norm(s) {
    return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }
  SUG.forEach(function (o) { o.n = norm(o.v); });

  var activo = -1;
  var items = [];

  function cerrar() {
    panel.hidden = true;
    panel.innerHTML = '';
    activo = -1;
    items = [];
  }

  function elegir(valor) {
    input.value = valor;
    cerrar();
    form.submit();
  }

  function pintar(lista) {
    if (!lista.length) { cerrar(); return; }
    panel.innerHTML = '';
    items = lista.map(function (o) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'emt-hero__sug-item';
      b.innerHTML = '<span class="emt-hero__sug-txt"></span><span class="emt-hero__sug-tag"></span>';
      b.querySelector('.emt-hero__sug-txt').textContent = o.v;
      b.querySelector('.emt-hero__sug-tag').textContent = o.t;
      b.addEventListener('click', function () { elegir(o.v); });
      panel.appendChild(b);
      return b;
    });
    activo = -1;
    panel.hidden = false;
  }

  function marcar(i) {
    items.forEach(function (el, j) { el.classList.toggle('is-active', j === i); });
    activo = i;
    if (i >= 0 && items[i]) { items[i].scrollIntoView({ block: 'nearest' }); }
  }

  input.addEventListener('input', function () {
    var q = norm(input.value.trim());
    if (q.length < 2) { cerrar(); return; }
    var empieza = [], contiene = [];
    for (var i = 0; i < SUG.length; i++) {
      if (SUG[i].n.indexOf(q) === 0) { empieza.push(SUG[i]); }
      else if (SUG[i].n.indexOf(q) > 0) { contiene.push(SUG[i]); }
    }
    pintar(empieza.concat(contiene).slice(0, 8));
  });

  input.addEventListener('keydown', function (e) {
    if (panel.hidden) { return; }
    if (e.key === 'ArrowDown') { e.preventDefault(); marcar(Math.min(activo + 1, items.length - 1)); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); marcar(Math.max(activo - 1, 0)); }
    else if (e.key === 'Enter' && activo >= 0) { e.preventDefault(); items[activo].click(); }
    else if (e.key === 'Escape') { cerrar(); }
  });

  document.addEventListener('click', function (e) {
    if (!form.contains(e.target)) { cerrar(); }
  });
})();
