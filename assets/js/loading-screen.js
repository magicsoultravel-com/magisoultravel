/* magic soul travel — loading screen driven by real content loads */
(function () {
  if (window.MST_LOADER) return;

  const overlay = document.getElementById('app-loader');
  if (!overlay) return; // only pages that declare #app-loader

  const fill = overlay.querySelector('.loader-fill');
  const pctEl = overlay.querySelector('.loader-pct');
  const stageEl = overlay.querySelector('.loader-stage');
  const enterBtn = overlay.querySelector('.loader-enter');

  const tasks = Object.create(null);
  let totalWeight = 0;
  let doneWeight = 0;
  const startedAt = Date.now();
  const MIN_VISIBLE = 1600;   // let the welcome write itself before "enter site" appears
  const HARD_TIMEOUT = 11000; // never leave the screen stuck
  let finished = false;
  let completed = false;
  let enterShown = false;

  function renderBar() {
    const p = totalWeight ? Math.min(100, (doneWeight / totalWeight) * 100) : 0;
    if (fill) fill.style.width = p + '%';
    if (pctEl) pctEl.textContent = Math.round(p) + '%';
  }
  function stageText(text) {
    if (stageEl && text) stageEl.textContent = text;
  }
  function pendingCount() {
    let n = 0;
    for (const k in tasks) if (!tasks[k].done) n++;
    return n;
  }
  function allDone() {
    return totalWeight > 0 && pendingCount() === 0;
  }

  // User clicked "enter site" — fade the screen away.
  function enterSite() {
    if (finished) return;
    finished = true;
    if (fill) fill.style.width = '100%';
    if (pctEl) pctEl.textContent = '100%';
    stageText('welcome in — enjoy the colours ✦');
    overlay.classList.remove('visible', 'ready');
    overlay.classList.add('loaded');
    overlay.setAttribute('aria-hidden', 'true');
    overlay.removeAttribute('aria-busy');
  }

  // Verification complete — reveal the handwritten "enter site" button.
  function showEnter() {
    if (finished || enterShown) return;
    enterShown = true;
    overlay.classList.add('ready');
    if (!enterBtn) return;
    enterBtn.innerHTML = '';

    const frag = document.createDocumentFragment();
    let i = 0;
    const pushChar = (ch, base) => {
      const s = document.createElement('span');
      s.className = base;
      s.textContent = ch;
      s.style.setProperty('--d', (base === 'le-star' ? i * 0.045 + 0.12 : i * 0.045) + 's');
      frag.appendChild(s);
      i++;
    };
    'enter site'.split(' ').forEach((word, wi) => {
      if (wi > 0) pushChar('\u00A0', 'le');
      [...word].forEach(ch => pushChar(ch, 'le'));
    });
    pushChar('✦', 'le-star');

    enterBtn.appendChild(frag);
    enterBtn.hidden = false;
    enterBtn.disabled = false;
    enterBtn.setAttribute('aria-hidden', 'false');
  }

  // Verification is complete — reveal the enter button shortly after.
  function complete() {
    if (finished || completed) return;
    completed = true;
    overlay.removeAttribute('aria-busy');
    stageText('welcome in — enjoy the colours ✦');
    const wait = Math.max(0, MIN_VISIBLE - (Date.now() - startedAt));
    setTimeout(showEnter, wait);
  }

  window.MST_LOADER = {
    register(name, weight, label) {
      if (tasks[name]) return;
      tasks[name] = { weight: Number(weight) || 1, done: false, label: label || '' };
      totalWeight += tasks[name].weight;
      overlay.classList.remove('hidden', 'loaded');
      overlay.classList.add('visible');
      overlay.setAttribute('aria-hidden', 'false');
      overlay.setAttribute('aria-busy', 'true');
      stageText(label);
      renderBar();
    },
    skipLoader() {
      // Jump to content if loader is visible
      if (!overlay.classList.contains('loaded')) {
        enterSite();
      }
    }
    done(name) {
      const t = tasks[name];
      if (!t || t.done) return;
      t.done = true;
      doneWeight += t.weight;
      let nextLabel = '';
      for (const k in tasks) {
        if (!tasks[k].done) { nextLabel = tasks[k].label; break; }
      }
      if (nextLabel) stageText(nextLabel);
      renderBar();
      if (allDone()) complete();
    },
    stage(text) { stageText(text); },
    setVideo(video, onPlay) {
      const wrap = overlay.querySelector('.loader-video');
      if (!wrap || !video || !video.videoId) return;
      const poster = wrap.querySelector('[data-video-poster]');
      const title = wrap.querySelector('[data-video-title]');
      if (poster) poster.style.backgroundImage = `url('https://i.ytimg.com/vi/${video.videoId}/hqdefault.jpg')`;
      if (title) title.textContent = video.title;
      const play = () => { if (typeof onPlay === 'function') onPlay(video.videoId, video.title); };
      wrap.addEventListener('click', play);
      wrap.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); play(); }
      });
      wrap.hidden = false;
    }
  };

  if (enterBtn) enterBtn.addEventListener('click', enterSite);

  // hard safety net — if a load never reports, still let the user in.
  setTimeout(complete, HARD_TIMEOUT);
})();