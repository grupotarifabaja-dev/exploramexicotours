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

    /* ---------- Medio único (foto de asesor, imagen/poster o video) vía wp.media ----------
       El tipo se define con data-media-type en el contenedor [data-image]
       ('image' por defecto; 'video' para el video del hero). */
    $(document).on('click', '[data-image-add]', function (e) {
      e.preventDefault();
      if (typeof wp === 'undefined' || !wp.media) { return; }
      var $wrap = $(this).closest('[data-image]');
      var type = $wrap.data('media-type') || 'image';
      var frame = wp.media({ title: 'Selecciona un archivo', multiple: false, library: { type: type } });
      frame.on('select', function () {
        var a = frame.state().get('selection').first().toJSON();
        var preview;
        if (type === 'video') {
          preview = '<video src="' + a.url + '" muted playsinline preload="metadata"></video>';
        } else {
          var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
          preview = '<img src="' + thumb + '" alt="" />';
        }
        $wrap.find('[data-image-preview]').html(preview);
        $wrap.find('[data-image-input]').val(a.id);
        $wrap.find('[data-image-remove]').show();
      });
      frame.open();
    });
    $(document).on('click', '[data-image-remove]', function () {
      var $wrap = $(this).closest('[data-image]');
      $wrap.find('[data-image-preview]').empty();
      $wrap.find('[data-image-input]').val('');
      $(this).hide();
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

    /* ---------- Guardado de formularios del panel (tour / asesor / config) ----------
       Genérico vía atributos data- en el <form>:
         data-ajax-action       acción admin-ajax
         data-required-draft     campos obligatorios al guardar borrador (coma)
         data-required-publish   campos obligatorios al publicar (coma)
       Nunca resetea lo capturado: si falta un obligatorio, marca y enfoca el campo. */
    var pendingStatus = 'draft';
    $(document).on('click', '[data-emt-form] [data-save]', function () {
      pendingStatus = $(this).data('save');
    });

    function reqList(form, status) {
      var attr = (status === 'publish') ? 'required-publish' : 'required-draft';
      var raw = ($(form).data(attr) || '').toString();
      return raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    }

    $(document).on('submit', '[data-emt-form]', function (e) {
      e.preventDefault();
      var form = this;
      var $form = $(form);
      var $msg = $form.find('[data-form-msg]');
      $form.find('.emt-field--error').removeClass('emt-field--error');

      var $firstErr = null;
      reqList(form, pendingStatus).forEach(function (name) {
        var $f = $form.find('[name="' + name + '"]');
        if (!$f.val() || !$f.val().trim()) {
          $f.closest('.emt-field').addClass('emt-field--error');
          if (!$firstErr) { $firstErr = $f; }
        }
      });
      if ($firstErr) {
        $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--err').text('Revisa los campos marcados (tus datos se conservan).');
        $firstErr.trigger('focus');
        if ($firstErr[0] && $firstErr[0].scrollIntoView) { $firstErr[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        return;
      }

      var data = new FormData(form);
      data.append('action', $form.data('ajax-action'));
      data.append('nonce', EMTPanel.nonce);
      data.append('post_id', $form.data('post-id') || 0);
      data.append('status', pendingStatus);

      $msg.attr('class', 'emt-panel-form__msg').text('Guardando…');
      $form.find('[data-save]').prop('disabled', true);

      $.ajax({ url: EMTPanel.ajax, method: 'POST', data: data, processData: false, contentType: false })
        .done(function (res) {
          if (res && res.success) {
            var okMsg = (res.data && res.data.msg) ? res.data.msg : 'Guardado.';
            if (res.data && res.data.editUrl) {
              $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--ok').text(okMsg + ' Redirigiendo…');
              window.location.href = res.data.editUrl;
            } else {
              $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--ok').text(okMsg);
            }
          } else {
            var m = (res && res.data && res.data.msg) ? res.data.msg : 'No se pudo guardar.';
            $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--err').text(m);
            if (res && res.data && res.data.field) {
              $form.find('[name="' + res.data.field + '"]').closest('.emt-field').addClass('emt-field--error');
            }
          }
        })
        .fail(function () {
          $msg.attr('class', 'emt-panel-form__msg emt-panel-form__msg--err').text('Error de conexión.');
        })
        .always(function () {
          $form.find('[data-save]').prop('disabled', false);
        });
    });

    /* ---------- Eliminar (tour / asesor) a la papelera ----------
       data-emt-delete="<acción ajax>"  data-id  data-title */
    $(document).on('click', '[data-emt-delete]', function () {
      var action = $(this).data('emt-delete');
      var id = $(this).data('id');
      var title = $(this).data('title') || 'este elemento';
      if (!window.confirm('¿Enviar "' + title + '" a la papelera?')) { return; }
      var $row = $(this).closest('tr');
      $.post(EMTPanel.ajax, { action: action, nonce: EMTPanel.nonce, id: id })
        .done(function (res) {
          if (res && res.success) { $row.fadeOut(200, function () { $(this).remove(); }); }
          else { window.alert((res && res.data && res.data.msg) || 'No se pudo eliminar.'); }
        })
        .fail(function () { window.alert('Error de conexión.'); });
    });
  });
})(jQuery);
