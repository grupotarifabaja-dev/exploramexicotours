/**
 * Filter bar del listado de tours (doc maestro §8.2).
 * Auto-envía el formulario al cambiar checkboxes/radios (mejora de UX; el form
 * también funciona sin JS con el botón "Aplicar").
 */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-emt-filters]');
    if (!form) {
      return;
    }
    form.addEventListener('change', function (e) {
      var t = e.target;
      if (t && (t.type === 'checkbox' || t.type === 'radio')) {
        form.submit();
      }
    });
  });
})();
