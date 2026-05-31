// ── Shared utilities ──────────────────────────────────────────────────────────

// Toast notification
function showToast(msg, type = '') {
  const c = document.getElementById('toast-container') || (() => {
    const el = document.createElement('div');
    el.id = 'toast-container';
    el.className = 'toast-container';
    document.body.appendChild(el);
    return el;
  })();
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}

// Navbar active link
function setActiveNav() {
  const path = window.location.pathname;
  document.querySelectorAll('.navbar-links a, .mobile-menu a').forEach(a => {
    a.classList.toggle('active', a.getAttribute('href') && path.endsWith(a.getAttribute('href')));
  });
}

// Mobile menu toggle
function initMobileMenu() {
  const toggle = document.getElementById('navbar-toggle');
  const menu   = document.getElementById('mobile-menu');
  if (!toggle || !menu) return;
  toggle.addEventListener('click', () => menu.classList.toggle('open'));
  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => menu.classList.remove('open')));
}

// Tab system
function initTabs(container = document) {
  container.querySelectorAll('.tabs-nav').forEach(nav => {
    nav.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        nav.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const panels = nav.closest('.tabs-wrapper')?.querySelectorAll('.tab-panel')
          || document.querySelectorAll('.tab-panel');
        panels.forEach(p => p.classList.toggle('active', p.dataset.panel === target));
      });
    });
  });
}

// Modal system
function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function initModals() {
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.modalOpen));
  });
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.modalClose));
  });
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.add('hidden');
    });
  });
}

// Animate elements on scroll
function initScrollAnimations() {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.style.animationPlayState = 'running'; obs.unobserve(e.target); }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.animate-up').forEach(el => {
    el.style.animationPlayState = 'paused';
    obs.observe(el);
  });
}

// Format number in Arabic
function formatNum(n) { return Number(n).toLocaleString('ar-DZ'); }
function formatDate(d) { return new Date(d).toLocaleDateString('ar-DZ'); }

// Wilaya list
const WILAYAS = [
  'الجزائر','وهران','قسنطينة','عنابة','البليدة','تيزي وزو','سطيف','باتنة','بجاية',
  'الشلف','تلمسان','مستغانم','سكيكدة','جيجل','برج بوعريريج','المدية','عين الدفلى',
  'بومرداس','تيبازة','المسيلة','بسكرة','ورقلة','غرداية','تمنراست','أدرار','إليزي'
];

// Init everything on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  setActiveNav();
  initMobileMenu();
  initTabs();
  initModals();
  initScrollAnimations();
});
