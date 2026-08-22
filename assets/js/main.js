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

// ----- Continuous smooth autoscroll (requestAnimationFrame-based) -----
// Moves the carousel at a constant pixel-per-second speed so there is
// never a "pause-then-snap" stepping effect.
// Uses a clone of the first slide for seamless infinite wrap-around.
function startContinuousAutoscroll(carousel, inner, container, speedPxPerSec) {
  const realSlides = container.querySelectorAll('.carousel-group').length;
  if (realSlides <= 1) return;                // nothing to scroll

  // Clone the first group and append it — when the RAF scroll passes the
  // last real slide it will show the clone. At that exact moment we
  // reset the transform to slide 0. Because clone === slide 0 the user
  // never sees a jump.
  const allOriginal = container.querySelectorAll('.carousel-group');
  const clone = allOriginal[0].cloneNode(true);
  clone.style.transform = '';                  // discard any stale inline styles
  // Strip map ids from the clone so Leaflet never tries to initialise on a
  // duplicate id when the clone enters the viewport.
  clone.querySelectorAll('.trip-map').forEach(el => el.removeAttribute('id'));
  inner.appendChild(clone);

  let offset = 0;
  let rafId = null;
  let running = true;

  const pause = () => { running = false; };
  const resume = () => { running = true; };

  // RAF owns the transform — the CSS transition would add latency
  inner.style.transition = 'none';

  function frame(timestamp, lastTime) {
    if (!running) {
      rafId = requestAnimationFrame((t) => frame(t, t));
      return;
    }
    const delta = lastTime ? (timestamp - lastTime) / 1000 : 0.016;
    offset += speedPxPerSec * delta;

    const innerWidth = inner.clientWidth || 1;
    const offsetPct = (offset / innerWidth) * 100;

    if (offsetPct >= 100) {
      offset = 0;
      const next = carousel.current() + 1;
      if (next >= realSlides) {
        // Past the last real slide — seamless wrap back to the first.
        // The clone (pixel-identical to slide 0) is currently visible,
        // so the instant reset is imperceptible.
        inner.style.transition = 'none';
        carousel.goTo(0);
        inner.style.transform = 'translateX(0%)';
      } else {
        carousel.goTo(next);
        inner.style.transform = `translateX(-${next * 100}%)`;
      }
    } else {
      inner.style.transform = `translateX(-${carousel.current() * 100 + offsetPct}%)`;
    }

    rafId = requestAnimationFrame((t) => frame(t, timestamp));
  }

  rafId = requestAnimationFrame((t) => frame(t, t));

  return { stop: () => { cancelAnimationFrame(rafId); }, pause, resume };
}

// ----- Skeleton loader helper -----
function showLoading(el, message = 'loading') {
  if (!el) return;
  el.innerHTML = `<div class="loading">${message}</div>`;
}

// ----- Autolink URLs in text (used for blog posts) -----
function autolink(text) {
  const urlPattern = /\b(?:https?:\/\/|www\.)\\S+\b/g;
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

// Helper: shuffle array (Fisher-Yates)
function shuffleArray(arr) {
  const shuffled = [...arr];
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
  }
  return shuffled;
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

      let shuffled = shuffleArray(images);
      let currentIdx = 0;

      let activeLayer = layer1;
      let inactiveLayer = layer2;

      // Show first (random) image immediately
      activeLayer.style.backgroundImage = `url('${shuffled[currentIdx]}')`;
      activeLayer.classList.add('active');

      function navigate(direction) {
        currentIdx = (currentIdx + direction + shuffled.length) % shuffled.length;
        
        // If we're wrapping around to reshuffle
        if (currentIdx === 0 && direction === 1) {
          shuffled = shuffleArray(shuffled);
        }

        inactiveLayer.style.backgroundImage = `url('${shuffled[currentIdx]}')`;
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
      // Shuffle and take 6 random
      const shuffled = remaining.sort(() => 0.5 - Math.random());
      const randomSix = shuffled.slice(0, 6);
      const wrap = document.getElementById('other-videos');
      wrap.innerHTML = randomSix.map(v => `
        <div class="video-item">
          <iframe src="https://www.youtube.com/embed/${v.videoId}" title="${escapeHtml(v.title)}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px;">
            <div class="video-title" style="padding: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; margin-right: 8px;">${escapeHtml(v.title)}</div>
            <a href="https://www.youtube.com/watch?v=${v.videoId}" target="_blank" rel="noopener noreferrer" style="font-size: 0.8rem; color: var(--accent); text-decoration: none; white-space: nowrap;" title="Watch on YouTube">YouTube ↗</a>
          </div>
        </div>
      `).join('');
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
window.MST = {
  showToast,
  initCarousel,
  startContinuousAutoscroll,
  autolink,
  escapeHtml,
  formatDate,
  showLoading,
  shuffleArray
};
