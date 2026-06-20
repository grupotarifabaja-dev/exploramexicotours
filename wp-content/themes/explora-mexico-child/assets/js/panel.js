/**
 * Panel de gestión EMT — interactividad.
 * P1: base. P2: filtro de la tabla. P3: formulario de tour (galería, itinerario, guardado AJAX).
 */
(function ($) {
  'use strict';
  $(function () {
    // Filtro en vivo de tablas del panel (P2).
    $('[data-emt-search]').on('input', function () {
      var q = $(this).val().toLowerCase();
      var target = $(this).data('emt-search');
      $(target).find('tbody tr').each(function () {
        var txt = $(this).text().toLowerCase();
        $(this).toggle(txt.indexOf(q) !== -1);
      });
    });
  });
})(jQuery);
