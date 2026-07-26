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
            '<div class="emt-gallery__item" data-att="' + a.id + '" draggable="true">' +
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
      // Si hay editor visual (TinyMCE), vuelca su contenido al textarea antes de leerlo.
      if (window.tinyMCE) { try { window.tinyMCE.triggerSave(); } catch (err) {} }
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
        var _errLang = $firstErr.closest('.emt-i18n-en').length ? 'en' : 'es';
        emtSetFormLang($form, _errLang);
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

    /* ---------- Pestañas de idioma (Español / English) ---------- */
    function emtSetFormLang($form, lang) {
      $form.toggleClass('emt-form--lang-en', lang === 'en');
      $form.find('[data-lang-tab]').each(function () {
        var active = $(this).data('lang-tab') === lang;
        $(this).toggleClass('is-active', active).attr('aria-selected', active ? 'true' : 'false');
      });
    }
    $(document).on('click', '[data-lang-tab]', function () {
      emtSetFormLang($(this).closest('form'), $(this).data('lang-tab'));
    });

    /* ---------- Clasificación: crear / renombrar / eliminar términos ---------- */
    function emtTermRowHtml(id, name, count, withDest) {
      var tours = count + ' tour' + (count === 1 ? '' : 's');
      var dest = withDest ?
        '<label class="emt-term-row__dest"><input type="checkbox" data-term-destacado value="1" /> <span>Destacado en home</span></label>' : '';
      return '<div class="emt-term-row" data-term-id="' + id + '">' +
        '<input type="text" class="emt-term-row__name" value="' + $('<i>').text(name).html() + '" data-term-name aria-label="Nombre" />' +
        '<span class="emt-term-row__count">' + tours + '</span>' +
        '<div class="emt-term-row__portada emt-image emt-image--xs" data-term-image>' +
          '<div class="emt-image__preview" data-image-preview></div>' +
          '<input type="hidden" value="0" data-image-input />' +
          '<button type="button" class="emt-panel__btn emt-panel__btn--sm" data-term-portada-add>Portada</button>' +
          '<button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-term-portada-remove style="display:none;">Quitar</button>' +
        '</div>' +
        dest +
        '<span class="emt-term-row__msg" data-term-msg></span>' +
        '<button type="button" class="emt-panel__btn emt-panel__btn--sm emt-panel__btn--danger" data-term-delete>Eliminar</button>' +
        '</div>';
    }
    // Agregar
    $(document).on('click', '[data-term-add]', function () {
      var $mgr = $(this).closest('[data-tax]');
      var tax = $mgr.data('tax');
      var $input = $mgr.find('[data-term-new]');
      var name = ($input.val() || '').trim();
      if (!name) { $input.trigger('focus'); return; }
      var $btn = $(this).prop('disabled', true);
      $.post(EMTPanel.ajax, { action: 'emt_panel_term_add', nonce: EMTPanel.nonce, taxonomy: tax, name: name })
        .done(function (res) {
          if (res && res.success && res.data) {
            $mgr.find('[data-tax-empty]').hide();
            var withDest = String($mgr.data('has-destacado')) === '1';
            $mgr.find('[data-tax-list]').append(emtTermRowHtml(res.data.term_id, res.data.name, res.data.count || 0, withDest));
            $input.val('').trigger('focus');
          } else {
            window.alert((res && res.data && res.data.msg) || 'No se pudo crear.');
          }
        })
        .fail(function () { window.alert('Error de conexión.'); })
        .always(function () { $btn.prop('disabled', false); });
    });
    // Renombrar al perder el foco (si cambió)
    $(document).on('change', '[data-term-name]', function () {
      var $row = $(this).closest('.emt-term-row');
      var $mgr = $row.closest('[data-tax]');
      var tax = $mgr.data('tax');
      var id = $row.data('term-id');
      var name = ($(this).val() || '').trim();
      var $msg = $row.find('[data-term-msg]').removeClass('is-err').text('Guardando…');
      $.post(EMTPanel.ajax, { action: 'emt_panel_term_rename', nonce: EMTPanel.nonce, taxonomy: tax, term_id: id, name: name })
        .done(function (res) {
          if (res && res.success) {
            $msg.removeClass('is-err').text('Guardado ✓');
            setTimeout(function () { $msg.text(''); }, 1800);
          } else {
            $msg.addClass('is-err').text((res && res.data && res.data.msg) || 'Error');
          }
        })
        .fail(function () { $msg.addClass('is-err').text('Sin conexión'); });
    });
    // Eliminar
    $(document).on('click', '[data-term-delete]', function () {
      var $row = $(this).closest('.emt-term-row');
      var $mgr = $row.closest('[data-tax]');
      var tax = $mgr.data('tax');
      var id = $row.data('term-id');
      var name = $row.find('[data-term-name]').val() || 'este elemento';
      if (!window.confirm('¿Eliminar "' + name + '"? Los tours dejarán de estar clasificados con él (no se borran los tours).')) { return; }
      $row.addClass('is-removing');
      $.post(EMTPanel.ajax, { action: 'emt_panel_term_delete', nonce: EMTPanel.nonce, taxonomy: tax, term_id: id })
        .done(function (res) {
          if (res && res.success) {
            $row.slideUp(180, function () {
              var $list = $mgr.find('[data-tax-list]');
              $row.remove();
              if (!$list.find('.emt-term-row').length) { $list.find('[data-tax-empty]').show(); }
            });
          } else {
            $row.removeClass('is-removing');
            window.alert((res && res.data && res.data.msg) || 'No se pudo eliminar.');
          }
        })
        .fail(function () { $row.removeClass('is-removing'); window.alert('Error de conexión.'); });
    });

    /* ---------- Portada por término (guarda al elegir la imagen) ---------- */
    $(document).on('click', '[data-term-portada-add]', function (e) {
      e.preventDefault();
      if (typeof wp === 'undefined' || !wp.media) { return; }
      var $wrap = $(this).closest('[data-term-image]');
      var $row = $(this).closest('.emt-term-row');
      var tax = $row.closest('[data-tax]').data('tax');
      var id = $row.data('term-id');
      var frame = wp.media({ title: 'Selecciona la portada', multiple: false, library: { type: 'image' } });
      frame.on('select', function () {
        var a = frame.state().get('selection').first().toJSON();
        var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
        $wrap.find('[data-image-preview]').html('<img src="' + thumb + '" alt="" />');
        $wrap.find('[data-image-input]').val(a.id);
        $wrap.find('[data-term-portada-remove]').show();
        var $msg = $row.find('[data-term-msg]').removeClass('is-err').text('Guardando…');
        $.post(EMTPanel.ajax, { action: 'emt_panel_term_portada', nonce: EMTPanel.nonce, taxonomy: tax, term_id: id, image_id: a.id })
          .done(function (res) {
            if (res && res.success) { $msg.text('Portada guardada ✓'); setTimeout(function(){ $msg.text(''); }, 1600); }
            else { $msg.addClass('is-err').text((res && res.data && res.data.msg) || 'Error'); }
          })
          .fail(function () { $msg.addClass('is-err').text('Sin conexión'); });
      });
      frame.open();
    });
    $(document).on('click', '[data-term-portada-remove]', function () {
      var $wrap = $(this).closest('[data-term-image]');
      var $row = $(this).closest('.emt-term-row');
      var tax = $row.closest('[data-tax]').data('tax');
      var id = $row.data('term-id');
      $wrap.find('[data-image-preview]').empty();
      $wrap.find('[data-image-input]').val('0');
      $(this).hide();
      var $msg = $row.find('[data-term-msg]').removeClass('is-err').text('Guardando…');
      $.post(EMTPanel.ajax, { action: 'emt_panel_term_portada', nonce: EMTPanel.nonce, taxonomy: tax, term_id: id, image_id: 0 })
        .done(function (res) {
          if (res && res.success) { $msg.text('Portada quitada ✓'); setTimeout(function(){ $msg.text(''); }, 1600); }
          else { $msg.addClass('is-err').text((res && res.data && res.data.msg) || 'Error'); }
        })
        .fail(function () { $msg.addClass('is-err').text('Sin conexión'); });
    });

    /* ---------- Destacado en home (solo destinos, guarda al momento) ---------- */
    $(document).on('change', '[data-term-destacado]', function () {
      var $row = $(this).closest('.emt-term-row');
      var id = $row.data('term-id');
      var on = $(this).is(':checked') ? 1 : 0;
      var $msg = $row.find('[data-term-msg]').removeClass('is-err').text('Guardando…');
      $.post(EMTPanel.ajax, { action: 'emt_panel_term_destacado', nonce: EMTPanel.nonce, term_id: id, on: on })
        .done(function (res) {
          if (res && res.success) { $msg.text('Guardado ✓'); setTimeout(function(){ $msg.text(''); }, 1600); }
          else { $msg.addClass('is-err').text((res && res.data && res.data.msg) || 'Error'); }
        })
        .fail(function () { $msg.addClass('is-err').text('Sin conexión'); });
    });

    /* ---------- Galería: arrastrar para reordenar (la primera = destacada) ---------- */
    var emtDragEl = null;
    $(document).on('dragstart', '.emt-gallery__item', function (e) {
      emtDragEl = this;
      $(this).addClass('is-dragging');
      try { e.originalEvent.dataTransfer.effectAllowed = 'move'; e.originalEvent.dataTransfer.setData('text/plain', ''); } catch (err) {}
    });
    $(document).on('dragend', '.emt-gallery__item', function () {
      $(this).removeClass('is-dragging');
      emtDragEl = null;
    });
    $(document).on('dragover', '.emt-gallery__item', function (e) {
      e.preventDefault();
      if (!emtDragEl || emtDragEl === this || this.parentNode !== emtDragEl.parentNode) { return; }
      var r = this.getBoundingClientRect();
      var before = e.originalEvent.clientY < r.top + r.height / 2 ||
        (Math.abs(e.originalEvent.clientY - (r.top + r.height / 2)) < 6 && e.originalEvent.clientX < r.left + r.width / 2);
      this.parentNode.insertBefore(emtDragEl, before ? this : this.nextSibling);
    });
    $(document).on('dragover', '[data-gallery-items]', function (e) { e.preventDefault(); });
  });
})(jQuery);
