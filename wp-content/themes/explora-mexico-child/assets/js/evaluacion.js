/**
 * Encuesta /evaluacion/: estrellas interactivas, validación mínima y envío
 * AJAX. La respuesta del servidor decide la pantalla final: buena experiencia
 * -> invitación a reseñar en Google; si no -> agradecimiento simple.
 */
(function () {
  'use strict';
  var form = document.querySelector('[data-eval-form]');
  if (!form) { return; }

  // Estrellas: pintar y guardar en el hidden correspondiente.
  form.querySelectorAll('[data-eval-stars]').forEach(function (grupo) {
    var target = form.querySelector('input[name="' + grupo.getAttribute('data-target') + '"]');
    var stars = Array.prototype.slice.call(grupo.querySelectorAll('.emt-eval__star'));
    function pintar(n) {
      stars.forEach(function (s, i) { s.classList.toggle('is-on', i < n); });
    }
    stars.forEach(function (s, i) {
      s.addEventListener('click', function () {
        if (target) { target.value = i + 1; }
        pintar(i + 1);
        grupo.classList.remove('is-error');
      });
      s.addEventListener('mouseenter', function () { pintar(i + 1); });
    });
    grupo.addEventListener('mouseleave', function () {
      pintar(parseInt(target && target.value, 10) || 0);
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var err = form.querySelector('[data-eval-err]');
    var calif = parseInt((form.querySelector('input[name="calif_general"]') || {}).value, 10) || 0;
    if (!calif) {
      if (err) { err.hidden = false; }
      var grupo = form.querySelector('[data-eval-stars][data-target="calif_general"]');
      if (grupo) { grupo.classList.add('is-error'); grupo.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
      return;
    }
    if (err) { err.hidden = true; }

    var btn = form.querySelector('[data-eval-enviar]');
    if (btn) { btn.disabled = true; btn.textContent = 'Enviando…'; }

    var url = (window.EMTEval && window.EMTEval.ajax) || '/wp-admin/admin-ajax.php';
    fetch(url, { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) { throw new Error((res && res.data && res.data.msg) || 'Error'); }
        form.hidden = true;
        var buena = form.parentElement.querySelector('[data-eval-final-buena]');
        var normal = form.parentElement.querySelector('[data-eval-final-normal]');
        if (res.data.buena && buena) {
          var link = buena.querySelector('[data-eval-review-link]');
          if (link && res.data.review) { link.href = res.data.review; }
          buena.hidden = false;
          buena.scrollIntoView({ block: 'center', behavior: 'smooth' });
        } else if (normal) {
          normal.hidden = false;
          normal.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
      })
      .catch(function (e2) {
        if (btn) { btn.disabled = false; btn.textContent = 'Enviar evaluación'; }
        window.alert(e2.message || 'No se pudo enviar, intenta de nuevo.');
      });
  });
})();
