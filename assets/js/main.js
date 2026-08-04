/* magic soul travel — shared utilities */

// ----- Toast notifications -----
function showToast(message, type = 'info', duration = 2500) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type === 'error' ? 'error' : type === 'success' ? 'success' : ''}`;
  toast.textContent = message;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(8px)';
    setTimeout(() => toast.remove(), 400);
  }, duration);
}

// ----- Mobile nav toggle -----
function initNav() {
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.main-nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', () => {
    nav.classList.toggle('open');
    toggle.textContent = nav.classList.contains('open') ? '✕' : '☰';
  });

  // Close nav when a link is clicked (mobile)
  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('open');
      toggle.textContent = '☰';
    });
  });
}

// ----- Active nav link -----
function setActiveNav() {
  const path = window.location.pathname.split('/').pop() || 'index.html';
  const current = path === '' ? 'index.html' : path;
  document.querySelectorAll('.main-nav a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === current || (current === 'index.html' && href === 'index.html')) {
      a.classList.add('active');
    }
  });
}

// ----- Generic carousel -----
function initCarousel(container) {
  if (!container) return;
  const inner = container.querySelector('.carousel-inner');
  const groups = Array.from(container.querySelectorAll('.carousel-group'));
  const prevBtn = container.querySelector('.prev-btn');
  const nextBtn = container.querySelector('.next-btn');
  const dotsWrap = container.querySelector('.carousel-dots');

  if (!inner || groups.length === 0) return;

  let current = 0;

  // Build dots if container has a dots wrapper
  if (dotsWrap && groups.length > 1) {
    groups.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.className = `carousel-dot ${i === 0 ? 'active' : ''}`;
      dot.setAttribute('aria-label', `Slide ${i + 1}`);
      dot.addEventListener('click', () => goTo(i));
      dotsWrap.appendChild(dot);
    });
  }

  function goTo(index) {
    if (index < 0) current = groups.length - 1;
    else if (index >= groups.length) current = 0;
    else current = index;

    inner.style.transform = `translateX(-${current * 100}%)`;

    // Update dots
    if (dotsWrap) {
      dotsWrap.querySelectorAll('.carousel-dot').forEach((dot, i) => {
        dot.classList.toggle('active', i === current);
      });
    }

    // Notify carousel consumer (e.g., lazy-load maps)
    container.dispatchEvent(new CustomEvent('carousel:change', {
      detail: { index: current, group: groups[current] }
    }));
  }

  if (prevBtn) prevBtn.addEventListener('click', () => goTo(current - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => goTo(current + 1));

  // Initial position
  goTo(0);

  return { goTo, current: () => current };
}

// ----- Skeleton loader helper -----
function showLoading(el, message = 'loading') {
  if (!el) return;
  el.innerHTML = `<div class="loading">${message}</div>`;
}

// ----- Autolink URLs in text (used for blog posts) -----
function autolink(text) {
  const urlPattern = /\b(?:https?:\/\/|www\.)\S+\b/g;
  return text.replace(urlPattern, (url) => {
    const href = url.startsWith('http') ? url : `https://${url}`;
    return `<a href="${href}" target="_blank" rel="noopener noreferrer">${url}</a>`;
  });
}

// ----- Escape HTML -----
function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// ----- Date formatting -----
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
}

// ----- Init on DOM ready -----
document.addEventListener('DOMContentLoaded', () => {
  initNav();
  setActiveNav();
});

// Expose for other scripts
window.MST = {
  showToast,
  initCarousel,
  autolink,
  escapeHtml,
  formatDate,
  showLoading
};