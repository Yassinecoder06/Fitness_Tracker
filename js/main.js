/* ===================================================
   FitTrack — Main JavaScript
   Sidebar toggle, progress animations, modals, interactions
   =================================================== */

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initActiveNav();
  initProgressBars();
  initCalorieRing();
  initAnimateIn();
  initWaterTracker();
  initModals();
  initCountUp();
});

/* ---------------------------------------------------
   SIDEBAR TOGGLE (Mobile)
   --------------------------------------------------- */
function initSidebar() {
  const hamburger = document.querySelector('.navbar__hamburger');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');

  if (!hamburger || !sidebar) return;

  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('active');
  });

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }
}

/* ---------------------------------------------------
   ACTIVE NAV LINK
   --------------------------------------------------- */
function initActiveNav() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const links = document.querySelectorAll('.sidebar__link');

  links.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index.html')) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

/* ---------------------------------------------------
   PROGRESS BARS — Animate on load
   --------------------------------------------------- */
function initProgressBars() {
  const fills = document.querySelectorAll('.progress-bar__fill');
  if (!fills.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const target = entry.target;
        const width = target.getAttribute('data-width');
        if (width) {
          setTimeout(() => {
            target.style.width = width + '%';
          }, 200);
        }
        observer.unobserve(target);
      }
    });
  }, { threshold: 0.2 });

  fills.forEach(fill => observer.observe(fill));
}

/* ---------------------------------------------------
   CALORIE RING — Animate stroke
   --------------------------------------------------- */
function initCalorieRing() {
  const ring = document.querySelector('.calorie-ring__fill');
  if (!ring) return;

  const percent = parseFloat(ring.getAttribute('data-percent')) || 0;
  const circumference = 2 * Math.PI * 80; // radius = 80
  const offset = circumference - (percent / 100) * circumference;

  setTimeout(() => {
    ring.style.strokeDashoffset = offset;
  }, 400);
}

/* ---------------------------------------------------
   ANIMATE IN — Staggered fade-in
   --------------------------------------------------- */
function initAnimateIn() {
  const items = document.querySelectorAll('.animate-in');
  if (!items.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  items.forEach(item => {
    item.style.opacity = '0';
    item.style.transform = 'translateY(16px)';
    observer.observe(item);
  });
}

/* ---------------------------------------------------
   WATER TRACKER
   --------------------------------------------------- */
function initWaterTracker() {
  const glasses = document.querySelectorAll('.water-tracker__glass');
  const countEl = document.querySelector('.water-tracker__count span');
  if (!glasses.length) return;

  glasses.forEach((glass, index) => {
    glass.addEventListener('click', () => {
      // Toggle: if clicking an already-filled glass that's the last filled one, unfill it
      const isFilled = glass.classList.contains('filled');
      const filledCount = document.querySelectorAll('.water-tracker__glass.filled').length;

      if (isFilled && index === filledCount - 1) {
        glass.classList.remove('filled');
      } else {
        // Fill all up to and including clicked index
        glasses.forEach((g, i) => {
          if (i <= index) {
            g.classList.add('filled');
          } else {
            g.classList.remove('filled');
          }
        });
      }

      const newCount = document.querySelectorAll('.water-tracker__glass.filled').length;
      if (countEl) countEl.textContent = newCount;
    });
  });
}

/* ---------------------------------------------------
   MODALS
   --------------------------------------------------- */
function initModals() {
  // Open buttons
  document.querySelectorAll('[data-modal]').forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      const modalId = trigger.getAttribute('data-modal');
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  // Close buttons
  document.querySelectorAll('.modal__close, .modal-cancel').forEach(btn => {
    btn.addEventListener('click', () => {
      const overlay = btn.closest('.modal-overlay');
      if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });

  // Click outside to close
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });
}

/* ---------------------------------------------------
   COUNT UP ANIMATION
   --------------------------------------------------- */
function initCountUp() {
  const counters = document.querySelectorAll('[data-count]');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute('data-count'), 10);
        const duration = 1200;
        const startTime = performance.now();

        function update(currentTime) {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);
          // Ease out cubic
          const eased = 1 - Math.pow(1 - progress, 3);
          const current = Math.floor(eased * target);
          el.textContent = current.toLocaleString();

          if (progress < 1) {
            requestAnimationFrame(update);
          } else {
            el.textContent = target.toLocaleString();
          }
        }

        requestAnimationFrame(update);
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.3 });

  counters.forEach(counter => observer.observe(counter));
}

/* ---------------------------------------------------
   CHART BARS — Animate on load
   --------------------------------------------------- */
function initChartBars() {
  const bars = document.querySelectorAll('.simple-chart__bar');
  if (!bars.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const height = el.getAttribute('data-height');
        if (height) {
          setTimeout(() => {
            el.style.height = height + '%';
          }, 200);
        }
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.1 });

  bars.forEach(bar => {
    bar.style.height = '0%';
    observer.observe(bar);
  });
}

// Also init chart bars on load
document.addEventListener('DOMContentLoaded', initChartBars);

/* ---------------------------------------------------
   FOOD FILTER CHIPS
   --------------------------------------------------- */
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('food-filter-chip')) {
    document.querySelectorAll('.food-filter-chip').forEach(c => c.classList.remove('active'));
    e.target.classList.add('active');
  }
});

/* ---------------------------------------------------
   TOAST / FEEDBACK (for save buttons)
   --------------------------------------------------- */
function showToast(message) {
  // Remove existing toast
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  toast.style.cssText = `
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #1F2937;
    color: #fff;
    padding: 14px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    z-index: 5000;
    opacity: 0;
    transform: translateY(12px);
    transition: all 0.3s ease;
  `;
  document.body.appendChild(toast);

  requestAnimationFrame(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
  });

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(12px)';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

// Attach to save buttons
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('btn--save')) {
    e.preventDefault();
    showToast('✓ Changes saved successfully!');
  }
});
