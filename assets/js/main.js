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

// ----- Background slideshow -----
function initBackgroundSlideshow() {
  const slideshow = document.createElement('div');
  slideshow.id = 'bg-slideshow';

  // Two layers for smooth crossfade
  const layer1 = document.createElement('div');
  layer1.className = 'bg-layer';
  const layer2 = document.createElement('div');
  layer2.className = 'bg-layer';
  slideshow.appendChild(layer1);
  slideshow.appendChild(layer2);

  // Navigation arrows
  const prevBtn = document.createElement('button');
  prevBtn.className = 'bg-nav bg-nav-prev';
  prevBtn.setAttribute('aria-label', 'Previous background');
  prevBtn.innerHTML = '‹';
  const nextBtn = document.createElement('button');
  nextBtn.className = 'bg-nav bg-nav-next';
  nextBtn.setAttribute('aria-label', 'Next background');
  nextBtn.innerHTML = '›';
  slideshow.appendChild(prevBtn);
  slideshow.appendChild(nextBtn);

  document.body.insertBefore(slideshow, document.body.firstChild);

  fetch('assets/data/backgrounds.json')
    .then(r => r.json())
    .then(images => {
      if (!images || images.length === 0) return;

      let current = 0;
      let activeLayer = layer1;
      let inactiveLayer = layer2;

      // Show first image immediately
      activeLayer.style.backgroundImage = `url('${images[current]}')`;
      activeLayer.classList.add('active');

      function navigate(direction) {
        current = (current + direction + images.length) % images.length;
        inactiveLayer.style.backgroundImage = `url('${images[current]}')`;
        inactiveLayer.classList.add('active');
        activeLayer.classList.remove('active');
        // Swap layers for next transition
        const temp = activeLayer;
        activeLayer = inactiveLayer;
        inactiveLayer = temp;
      }

      prevBtn.addEventListener('click', () => navigate(-1));
      nextBtn.addEventListener('click', () => navigate(1));

      // Auto-rotate every 60 seconds
      setInterval(() => navigate(1), 60000);
    })
    .catch(() => {
      // No background slideshow — silently skip
    });
}

// ----- Init on DOM ready -----
document.addEventListener('DOMContentLoaded', () => {
  initNav();
  setActiveNav();


// Other Videos — random selection from remaining after excluding latest 6
function initOtherVideos() {
  fetch('assets/data/videos.json')
    .then(r => r.json())
    .then(videos => {
      const remaining = videos.slice(6); // exclude the latest 6
      if (remaining.length === 0) {
        document.getElementById('other-videos').innerHTML = '<p class="text-dim">no other videos yet</p>';
        return;
      }
      # Shuffle and take 6 random
      shuffled = remaining.sort(() => 0.5 - Math.random())
      randomSix = shuffled.slice(0, 6)
      wrap = document.getElementById('other-videos')
      wrap.innerHTML = randomSix.map(v => '<div class="video-item">
          <iframe src="https://www.youtube.com/embed/" + v.videoId + "" title=""" + v.title.replace(/"\/g, '"') + """ loading="lazy" allowfullscreen></iframe>
          <div class="video-title">"" + v.title + ""</div>
        </div>'
    ).join('');
    })
    .catch(() => {
      document.getElementById('other-videos').innerHTML = '<p class="text-dim">videos unavailable right now</p>';
    });
}

// Call on DOMContentLoaded if on index page
document.addEventListener('DOMContentLoaded', () => {
  initNav();
  setActiveNav();
  initBackgroundSlideshow();
  const otherGrid = document.getElementById('other-videos');
  if (otherGrid) {
    initOtherVideos();
  }
});


// Expose for other scripts

// Expose for other scripts
window.MST = {
  showToast,
  initCarousel,
  autolink,
  escapeHtml,
  formatDate,
  showLoading
};