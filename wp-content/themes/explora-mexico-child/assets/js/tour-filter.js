/**
 * Filtros del catálogo de tours con AJAX + "cargar más" (Fase D).
 * Mejora progresiva: sin JS el formulario funciona por GET y hay paginación.
 * Con JS: filtra sin recargar, actualiza la URL (pushState), soporta el botón
 * atrás (popstate), "cargar más" y un panel de filtros colapsable en móvil.
 *
 * Rediseño 2026 (filtros que escalan): grupos colapsables (acordeón), "ver más"
 * para listas largas, buscador dentro de Destinos y chips de filtros ACTIVOS
 * arriba de los resultados. La clase `emt-filters--js` activa el CSS de mejora.
 */
(function () {
  'use strict';

  var listing = document.querySelector('[data-emt-listing]');
  if (!listing) { return; }
  var form = listing.querySelector('[data-emt-filters]');
  var results = listing.querySelector('[data-emt-results]');
  var grid = listing.querySelector('[data-emt-grid]');
  var countEl = listing.querySelector('[data-emt-count]');
  var emptyEl = listing.querySelector('[data-emt-empty]');
  var loadmore = listing.querySelector('[data-emt-loadmore]');
  var pagination = listing.querySelector('[data-emt-pagination]');
  var activeWrap = listing.querySelector('[data-emt-active]');
  if (!form || !results || !grid) { return; }

  form.classList.add('emt-filters--js'); // habilita el CSS de mejora progresiva

  var ajaxUrl = form.getAttribute('data-ajax');
  var nonce = form.getAttribute('data-nonce');
  if (!ajaxUrl || !nonce) { return; } // sin datos: se queda el GET normal

  var MULTI = ['destino', 'categoria', 'experiencia', 'duracion'];
  var current = null; // AbortController de la petición en curso
  var searchTimer = null;

  /* -------- Slider de rango de precio (doble) -------- */
  var range = form.querySelector('[data-emt-range]');
  var rMin = range ? parseInt(range.getAttribute('data-min'), 10) : 0;
  var rMax = range ? parseInt(range.getAttribute('data-max'), 10) : 0;
  function money(n) { return '$' + Number(n).toLocaleString('es-MX'); }
  function paintRange() {
    if (!range) { return; }
    var lo = range.querySelector('[data-range-min]');
    var hi = range.querySelector('[data-range-max]');
    var fill = range.querySelector('[data-range-fill]');
    var a = parseInt(lo.value, 10), b = parseInt(hi.value, 10);
    if (a > b) {
      if (document.activeElement === lo) { a = b; lo.value = b; } else { b = a; hi.value = a; }
    }
    if (fill && rMax > rMin) {
      fill.style.left = ((a - rMin) / (rMax - rMin) * 100) + '%';
      fill.style.right = (100 - (b - rMin) / (rMax - rMin) * 100) + '%';
    }
    var loL = range.querySelector('[data-range-lo]');
    var hiL = range.querySelector('[data-range-hi]');
    if (loL) { loL.textContent = money(a); }
    if (hiL) { hiL.textContent = money(b); }
  }
  if (range) {
    range.querySelector('[data-range-min]').addEventListener('input', paintRange);
    range.querySelector('[data-range-max]').addEventListener('input', paintRange);
    paintRange();
  }

  /* -------- Utilidades -------- */
  // Parámetros para la URL (limpios: sin base_*, sin vacíos, sin rango por defecto).
  function urlParams() {
    var fd = new FormData(form);
    var p = new URLSearchParams();
    fd.forEach(function (value, key) {
      if (key === 'base_tax' || key === 'base_term' || value === '' || value == null) { return; }
      if (key === 'precio_min' && parseInt(value, 10) <= rMin) { return; }
      if (key === 'precio_max' && parseInt(value, 10) >= rMax) { return; }
      p.append(key, value);
    });
    return p;
  }

  function setLoading(on) {
    results.setAttribute('aria-busy', on ? 'true' : 'false');
    results.classList.toggle('is-loading', !!on);
  }

  function updateLoadMore(page, maxPages) {
    if (!loadmore) { return; }
    loadmore.setAttribute('data-page', page);
    loadmore.setAttribute('data-max', maxPages);
    if (page < maxPages) { loadmore.hidden = false; } else { loadmore.hidden = true; }
    // Con JS activo, la paginación clásica se oculta a favor de "cargar más".
    if (pagination) { pagination.hidden = true; }
  }

  // Petición AJAX. mode: 'replace' | 'append'.
  function fetchTours(page, mode, push) {
    if (current) { current.abort(); }
    current = ('AbortController' in window) ? new AbortController() : null;

    var body = new FormData(form);
    body.append('action', 'emt_filter_tours');
    body.append('nonce', nonce);
    body.set('paged', page);

    setLoading(true);
    fetch(ajaxUrl, {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      signal: current ? current.signal : undefined
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) { throw new Error('bad response'); }
        var d = res.data;
        if (mode === 'append') {
          grid.insertAdjacentHTML('beforeend', d.html);
        } else {
          grid.innerHTML = d.html;
        }
        if (countEl) { countEl.textContent = d.count_label; }
        if (emptyEl) { emptyEl.hidden = !d.empty; }
        updateLoadMore(d.page, d.max_pages);
        if (push) { pushUrl(); }
      })
      .catch(function (err) {
        if (err && err.name === 'AbortError') { return; }
        // Si el AJAX falla, dejamos que el formulario haga GET normal como respaldo.
        if (mode !== 'append') { form.submit(); }
      })
      .then(function () { setLoading(false); });
  }

  function pushUrl() {
    var qs = urlParams().toString();
    var url = window.location.pathname + (qs ? '?' + qs : '');
    window.history.pushState({ emt: true }, '', url);
  }

  function applyFilters() { fetchTours(1, 'replace', true); }

  /* -------- Chips de filtros ACTIVOS (arriba de los resultados) -------- */
  var A_LABEL = activeWrap ? (activeWrap.getAttribute('data-label') || '') : '';
  var A_CLEAR = activeWrap ? (activeWrap.getAttribute('data-clear') || '') : '';
  var A_REMOVE = activeWrap ? (activeWrap.getAttribute('data-remove') || '') : '';

  function chosenInputs() {
    return Array.prototype.slice.call(form.querySelectorAll('.emt-opt input')).filter(function (i) {
      return i.checked && i.value !== '';
    });
  }

  function renderActive() {
    if (!activeWrap) { return; }
    var chosen = chosenInputs();
    activeWrap.innerHTML = '';
    if (!chosen.length) { activeWrap.hidden = true; return; }
    activeWrap.hidden = false;

    var lbl = document.createElement('span');
    lbl.className = 'emt-active-filters__label';
    lbl.textContent = A_LABEL;
    activeWrap.appendChild(lbl);

    chosen.forEach(function (inp) {
      var opt = inp.closest('.emt-opt');
      var nameEl = opt ? opt.querySelector('.emt-opt__name') : null;
      var text = nameEl ? nameEl.textContent.trim() : inp.value;
      var chip = document.createElement('span');
      chip.className = 'emt-active-chip';
      chip.appendChild(document.createTextNode(text));
      var x = document.createElement('button');
      x.type = 'button';
      x.className = 'emt-active-chip__x';
      x.innerHTML = '&times;';
      x.setAttribute('aria-label', (A_REMOVE + ' ' + text).trim());
      x.addEventListener('click', function () {
        if (inp.type === 'radio') {
          var reset = form.querySelector('input[name="' + inp.name + '"][value=""]');
          if (reset) { reset.checked = true; }
        } else {
          inp.checked = false;
        }
        renderActive();
        applyFilters();
      });
      chip.appendChild(x);
      activeWrap.appendChild(chip);
    });

    var clr = document.createElement('button');
    clr.type = 'button';
    clr.className = 'emt-active-filters__clear';
    clr.textContent = A_CLEAR;
    clr.addEventListener('click', function () { clearAll(); });
    activeWrap.appendChild(clr);
  }

  function clearAll() {
    form.querySelectorAll('input[type="checkbox"]').forEach(function (i) { i.checked = false; });
    form.querySelectorAll('input[type="radio"]').forEach(function (i) { i.checked = (i.value === ''); });
    var q = form.querySelector('input[name="q"]');
    if (q) { q.value = ''; }
    if (range) {
      range.querySelector('[data-range-min]').value = rMin;
      range.querySelector('[data-range-max]').value = rMax;
      paintRange();
    }
    renderActive();
    applyFilters();
  }

  /* -------- Sincroniza el formulario desde la URL (para popstate) -------- */
  function syncFormFromUrl() {
    var params = new URLSearchParams(window.location.search);
    var q = form.querySelector('input[name="q"]');
    if (q) { q.value = params.get('q') || ''; }
    var dif = params.get('dificultad') || '';
    form.querySelectorAll('input[name="dificultad"]').forEach(function (i) { i.checked = (i.value === dif); });
    MULTI.forEach(function (name) {
      var vals = params.getAll(name + '[]');
      form.querySelectorAll('input[name="' + name + '[]"]').forEach(function (i) { i.checked = vals.indexOf(i.value) !== -1; });
    });
    if (range) {
      var lo = range.querySelector('[data-range-min]');
      var hi = range.querySelector('[data-range-max]');
      lo.value = params.get('precio_min') || rMin;
      hi.value = params.get('precio_max') || rMax;
      paintRange();
    }
    renderActive();
  }

  /* -------- Acordeón, "ver más" y buscador dentro de grupos -------- */
  form.querySelectorAll('[data-facet-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var facet = btn.closest('[data-emt-facet]');
      var open = facet.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  form.querySelectorAll('[data-facet-more]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('[data-emt-facet]').classList.toggle('show-all');
    });
  });
  form.querySelectorAll('[data-facet-search]').forEach(function (inp) {
    inp.addEventListener('input', function () {
      var facet = inp.closest('[data-emt-facet]');
      var q = inp.value.trim().toLowerCase();
      var items = facet.querySelectorAll('.emt-opt-item');
      var visibles = 0;
      facet.classList.toggle('is-searching', q !== '');
      items.forEach(function (it) {
        var nameEl = it.querySelector('.emt-opt__name');
        var match = nameEl && nameEl.textContent.toLowerCase().indexOf(q) !== -1;
        it.classList.toggle('is-hidden', !match);
        if (match) { visibles++; }
      });
      var nomatch = facet.querySelector('[data-facet-nomatch]');
      if (nomatch) { nomatch.hidden = (visibles !== 0); }
      var more = facet.querySelector('[data-facet-more]');
      if (more) { more.style.display = q ? 'none' : ''; }
    });
  });

  /* -------- Eventos -------- */
  form.addEventListener('change', function (e) {
    var t = e.target;
    if (!t) { return; }
    if (t.type === 'checkbox' || t.type === 'radio') { renderActive(); applyFilters(); }
    else if (t.type === 'range') { applyFilters(); }
  });
  // Búsqueda de tours (texto libre) con debounce.
  form.addEventListener('input', function (e) {
    if (e.target && e.target.name === 'q') {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(applyFilters, 400);
    }
  });
  // Evita el submit real (queda como respaldo si el JS falla antes de este punto).
  form.addEventListener('submit', function (e) { e.preventDefault(); applyFilters(); });

  if (loadmore) {
    loadmore.addEventListener('click', function () {
      var next = parseInt(loadmore.getAttribute('data-page'), 10) + 1;
      fetchTours(next, 'append', false);
    });
  }

  var clearLink = form.querySelector('[data-emt-clear]');
  if (clearLink) {
    clearLink.addEventListener('click', function (e) {
      e.preventDefault();
      clearAll();
    });
  }

  window.addEventListener('popstate', function () {
    syncFormFromUrl();
    fetchTours(1, 'replace', false);
  });

  /* -------- Panel de filtros colapsable (móvil) -------- */
  var toggle = listing.querySelector('[data-emt-filters-toggle]');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = listing.classList.toggle('emt-filters--open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Al cargar con JS, revela "cargar más" si hay más páginas y oculta la paginación.
  if (loadmore) {
    updateLoadMore(parseInt(loadmore.getAttribute('data-page'), 10), parseInt(loadmore.getAttribute('data-max'), 10));
  }

  // Estado inicial de los chips activos (si se llegó con filtros en la URL).
  renderActive();
})();
