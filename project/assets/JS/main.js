// =============================================
// TicketApp — Hoofd JavaScript
// =============================================

document.addEventListener('DOMContentLoaded', () => {

  // ---- Navbar: scroll effect ----
  const navbar = document.getElementById('navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
  }

  // ---- Hamburger menu (mobiel) ----
  const toggle = document.getElementById('navbarToggle');
  const nav    = document.getElementById('navbarNav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      nav.classList.toggle('open');
      const isOpen = nav.classList.contains('open');
      toggle.setAttribute('aria-expanded', isOpen);
    });
    // Sluit menu bij klik buiten
    document.addEventListener('click', (e) => {
      if (!toggle.contains(e.target) && !nav.contains(e.target)) {
        nav.classList.remove('open');
      }
    });
  }

  // ---- User dropdown menu ----
  const userBtn      = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');
  if (userBtn && userDropdown) {
    userBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => {
      userDropdown.classList.remove('open');
    });
  }

  // ---- Flash message: automatisch sluiten na 5s ----
  const flash = document.getElementById('flashMessage');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = 'opacity 0.4s ease';
      flash.style.opacity = '0';
      setTimeout(() => flash.remove(), 400);
    }, 5000);
  }

  // ---- Fade-in animaties via IntersectionObserver ----
  const fadeEls = document.querySelectorAll('.fade-in');
  if (fadeEls.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    fadeEls.forEach(el => {
      el.style.opacity = '0';
      observer.observe(el);
    });
  }

  // ---- Teller animatie voor statistieken ----
  function animateTeller(el, eindeWaarde, duur = 1200, prefix = '', suffix = '') {
    const startTijd = performance.now();
    const startWaarde = 0;

    function stap(tijdstip) {
      const verstreken = tijdstip - startTijd;
      const voortgang  = Math.min(verstreken / duur, 1);
      // Ease-out cubic
      const factor = 1 - Math.pow(1 - voortgang, 3);
      const huidig = Math.round(startWaarde + (eindeWaarde - startWaarde) * factor);
      el.textContent = prefix + huidig.toLocaleString('nl-NL') + suffix;
      if (voortgang < 1) requestAnimationFrame(stap);
    }
    requestAnimationFrame(stap);
  }

  // Activeer tellers als ze in beeld komen
  const tellerEls = document.querySelectorAll('[data-teller]');
  if (tellerEls.length && 'IntersectionObserver' in window) {
    const tellerObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el     = entry.target;
          const waarde  = parseFloat(el.dataset.teller);
          const prefix  = el.dataset.prefix || '';
          const suffix  = el.dataset.suffix || '';
          animateTeller(el, waarde, 1400, prefix, suffix);
          tellerObserver.unobserve(el);
        }
      });
    }, { threshold: 0.3 });
    tellerEls.forEach(el => tellerObserver.observe(el));
  }

  // ---- Formulier validatie helper ----
  window.valideerFormulier = function(formId, regels) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let geldig = true;
    // Verwijder eerdere fouten
    form.querySelectorAll('.form-error').forEach(e => e.remove());
    form.querySelectorAll('.form-control').forEach(e => e.classList.remove('invalid'));

    regels.forEach(({ veld, test, bericht }) => {
      const input = form.querySelector(`[name="${veld}"]`);
      if (input && !test(input.value)) {
        geldig = false;
        input.classList.add('invalid');
        const err = document.createElement('p');
        err.className = 'form-error';
        err.textContent = bericht;
        input.parentElement.appendChild(err);
      }
    });
    return geldig;
  };

  // ---- Bevestigingsdialoog voor verwijderen ----
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      if (!confirm(btn.dataset.confirm)) {
        e.preventDefault();
      }
    });
  });

});
