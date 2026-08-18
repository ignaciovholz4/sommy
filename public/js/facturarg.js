/* ============================================================
   FACTURARG · Landing JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── AOS ──────────────────────────────────────────────── */
  if (window.AOS) {
    AOS.init({
      duration: 700,
      easing: 'ease-out-cubic',
      once: true,
      offset: 60,
      disable: window.innerWidth < 640 ? 'phone' : false,
    });
  }

  /* ── Mega menu (hover on desktop, click everywhere) ──── */
  const triggers = document.querySelectorAll('.nav-links .has-mega');
  const panels = document.querySelectorAll('.mega-panel');
  let closeTimer = null;

  const closeAll = () => {
    triggers.forEach(t => t.classList.remove('is-open'));
    panels.forEach(p => p.classList.remove('is-open'));
  };

  const openMega = (trigger) => {
    clearTimeout(closeTimer);
    closeAll();
    trigger.classList.add('is-open');
    const id = 'mega-' + trigger.dataset.mega;
    const panel = document.getElementById(id);
    if (panel) panel.classList.add('is-open');
  };

  const scheduleClose = () => {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(closeAll, 200);
  };

  triggers.forEach(trigger => {
    const btn = trigger.querySelector('button');
    trigger.addEventListener('mouseenter', () => openMega(trigger));
    trigger.addEventListener('mouseleave', scheduleClose);
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const isOpen = trigger.classList.contains('is-open');
      if (isOpen) closeAll();
      else openMega(trigger);
    });
  });

  panels.forEach(panel => {
    panel.addEventListener('mouseenter', () => clearTimeout(closeTimer));
    panel.addEventListener('mouseleave', scheduleClose);
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-links') && !e.target.closest('.mega-panel')) {
      closeAll();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
  });

  /* ── Pricing toggle Mensual / Anual ──────────────────── */
  const toggleBtns = document.querySelectorAll('.price-toggle-btn');
  const amounts = document.querySelectorAll('.plan-amount');

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const period = btn.dataset.period;
      toggleBtns.forEach(b => {
        const active = b === btn;
        b.classList.toggle('is-active', active);
        b.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      amounts.forEach(el => {
        const next = el.dataset[period];
        if (!next) return;
        // tiny morph
        el.style.transition = 'opacity .18s, transform .18s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-4px)';
        setTimeout(() => {
          el.textContent = next;
          el.style.opacity = '1';
          el.style.transform = 'translateY(0)';
        }, 160);
      });
    });
  });

});
