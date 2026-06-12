/**
 * EMT — Under Construction Script
 */
(function(){
  'use strict';

  // Animación de contadores
  const counters = document.querySelectorAll('.counter-value');
  const animateCounter = (el) => {
    const target = parseInt(el.getAttribute('data-target'));
    const duration = 2200;
    const start = performance.now();
    const update = (now) => {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = Math.floor(target * eased);
      el.textContent = value >= 1000 ? value.toLocaleString('es-MX') + '+' : value + '+';
      if (progress < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(c => observer.observe(c));

  // Form handler con WP REST API
  const form = document.getElementById('emtLeadForm');
  const msg = document.getElementById('emtFormMsg');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const endpoint = form.dataset.endpoint;
      const nonce = form.dataset.nonce;
      const email = form.querySelector('input[name="email"]').value;
      const btn = form.querySelector('button');

      btn.disabled = true;
      btn.textContent = 'Enviando...';

      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce
          },
          body: JSON.stringify({ email })
        });

        const data = await response.json();

        if (response.ok && data.success) {
          msg.textContent = '✓ ' + data.message;
          msg.style.color = '#8BC34A';
          form.reset();
        } else {
          msg.textContent = data.message || 'Ocurrió un error. Intenta de nuevo.';
          msg.style.color = '#F28C00';
        }
      } catch (err) {
        msg.textContent = 'No pudimos conectar. Intenta de nuevo en un momento.';
        msg.style.color = '#F28C00';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Avísenme';
      }
    });
  }
})();
