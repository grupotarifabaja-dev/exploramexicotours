/**
 * Panel de gestión EMT — interactividad.
 * Filtro de tablas, repeaters (itinerario/incluye), galería (wp.media),
 * guardado AJAX del tour y eliminación.
 */
(function ($) {
  'use strict';

  $(function () {
    /* ---------- Filtro en vivo de tablas ---------- */
    $('[data-emt-search]').on('input', function () {
      var q = $(this).val().toLowerCase();
      $($(this).data('emt-search')).find('tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
      });
    });

    /* ---------- Repeaters (itinerario / incluye / no_incluye) ---------- */
    var rowSeq = Date.now();
    $(document).on('click', '[data-repeater-add]', function () {
      var name = $(this).data('repeater-add');
      var tpl = document.getElementById('emt-tpl-' + name);
      var container = document.querySelector('[data-repeater="' + name + '"]');
      if (!tpl || !container) { return; }
      var i = rowSeq++;
      var html = tpl.innerHTML.replace(/__i__/g, i);
      var wrap = document.createElement('div');
      wrap.innerHTML = html.trim();
      var node = wrap.firstChild;
      // Convierte data-name="a|i|b" -> name="a[i][b]"
      node.querySelectorAll('[data-name]').forEach(function (el) {
        var parts = el.getAttribute('data-name').split('|');
        el.setAttribute('name', parts[0] + '[' + parts[1] + '][' + parts[2] + ']');
        el.removeAttribute('data-name');
      });
      container.appendChild(node);
    });
    $(document).on('click', '[data-remove]', function () {
      $(this).closest('[data-row]').remove();
    });

    /* ---------- Galería (librería de medios de WP) ---------- */
    $(document).on('click', '[data-gallery-add]', function (e) {
      e.preventDefault();
      if (typeof wp === 'undefined' || !wp.media) { return; }
      var frame = wp.media({ title: 'Selecciona o sube fotos', multiple: true, library: { type: 'image' } });
      frame.on('select', function () {
        var items = $('[data-gallery-items]');
        frame.state().get('selection').each(function (att) {
          var a = att.toJSON();
          var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
          items.append(
            '<div class="emt-gallery__item" data-att="' + a.id + '">' +
            '<img src="' + thumb + '" alt="" />' +
            '<button type="button" data-remove-img>&times;</button>' +
            '<input type="hidden" name="galeria[]" value="' + a.id + '" />' +
            '</div>'
          );
        });
      });
      frame.open();
    });
    $(document).on('click', '[data-remove-img]', function () {
      $(this).closest('.emt-gallery__item').remove();
    });

    /* ---------- Guardado del tour ---------- */
    var pendingStatus = 'draft';
    $(document).on('click', '#emt-tour-form [data-save]', function () {
      pendingStatus = $(this).data('save');
    });

    $(document).on('submit', '#emt-tour-form', function (e) {
      e.preventDefault();
      var form = this;
      var $msg = $(form).find('[data-form-msg]');
      $(form).find('.emt-field--error').removeClass('emt-field--error');

      // Validación de obligatorios.
      var ok = true;
      $(form).find('[name="titulo"],[name="duracion_texto"]').each(function () {
        if (!$(this).val().trim()) { $(this).closest('.emt-field').addClass('emt-field--error'); ok = false; }
      });
      if (!ok) {
        $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--err').text('Revisa los campos obligatorios.');
        return;
      }

      var data = new FormData(form);
      data.append('action', 'emt_panel_save_tour');
      data.append('nonce', EMTPanel.nonce);
      data.append('post_id', $(form).data('post-id') || 0);
      data.append('status', pendingStatus);

      $msg.attr('class', 'emt-panel-form__msg').text('Guardando…');
      $(form).find('[data-save]').prop('disabled', true);

      $.ajax({ url: EMTPanel.ajax, method: 'POST', data: data, processData: false, contentType: false })
        .done(function (res) {
          if (res && res.success) {
            $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--ok').text(res.data.msg + ' Redirigiendo…');
            window.location.href = res.data.editUrl;
          } else {
            var m = (res && res.data && res.data.msg) ? res.data.msg : 'No se pudo guardar.';
            $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--err').text(m);
            if (res && res.data && res.data.field) {
              $(form).find('[name="' + res.data.field + '"]').closest('.emt-field').addClass('emt-field--error');
            }
          }
        })
        .fail(function () {
          $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--err').text('Error de conexión.');
        })
        .always(function () {
          $(form).find('[data-save]').prop('disabled', false);
        });
    });

    /* ---------- Eliminar tour ---------- */
    $(document).on('click', '[data-emt-delete-tour]', function () {
      var id = $(this).data('emt-delete-tour');
      var title = $(this).data('title') || 'este tour';
      if (!window.confirm('¿Enviar "' + title + '" a la papelera?')) { return; }
      var $row = $(this).closest('tr');
      $.post(EMTPanel.ajax, { action: 'emt_panel_delete_tour', nonce: EMTPanel.nonce, id: id })
        .done(function (res) {
          if (res && res.success) { $row.fadeOut(200, function () { $(this).remove(); }); }
          else { window.alert((res && res.data && res.data.msg) || 'No se pudo eliminar.'); }
        })
        .fail(function () { window.alert('Error de conexión.'); });
    });
  });
})(jQuery);
