/* magic soul travel — holiday planner (calendar with localStorage) */

(function () {
  const STORAGE_KEY = 'mst_holiday_data';

  let currentYear = new Date().getFullYear();
  let selectedColor = '#ff0000';
  let secondColor = null;
  let twoColorMode = false;
  let savedData = [];

  const DEFAULT_COLORS = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ff8000', '#ff0080'];
  const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const WEEKDAYS = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];

  // ----- LocalStorage helpers -----
  function loadSaved() {
    try {
      savedData = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch (e) {
      savedData = [];
    }
  }

  function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(savedData));
  }

  // ----- DOM helpers -----
  function el(sel, root = document) {
    return root.querySelector(sel);
  }

  function colorBtnHTML(color) {
    return `<button type="button" class="color-btn" style="background: ${color};" data-color="${color}" title="${color}"></button>`;
  }

  // ----- Calendar rendering -----
  function renderCalendar() {
    const container = el('#calendar-container');
    if (!container) return;

    container.innerHTML = '';

    for (let m = 0; m < 12; m++) {
      const firstDay = new Date(currentYear, m, 1);
      const daysInMonth = new Date(currentYear, m + 1, 0).getDate();
      const startDow = firstDay.getDay();
      const weeks = Math.ceil((daysInMonth + startDow) / 7);

      let html = `<div class="calendar-month">
        <h3>${MONTHS[m]}</h3>
        <table>
          <thead><tr>`;

      WEEKDAYS.forEach((day, i) => {
        html += `<th class="${(i === 0 || i === 6) ? 'weekend' : ''}">${day}</th>`;
      });
      html += '</tr></thead><tbody>';

      let day = 1;
      for (let w = 0; w < weeks; w++) {
        html += '<tr>';
        for (let d = 0; d < 7; d++) {
          if (w === 0 && d < startDow) {
            html += '<td class="empty"></td>';
          } else if (day <= daysInMonth) {
            const isWeekend = (d === 0 || d === 6);
            const dateStr = `${currentYear}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const entry = savedData.find(e => e.date === dateStr);
            let bg = '';
            if (entry) {
              if (entry.colors && entry.colors.length >= 2) {
                bg = ` style="background: linear-gradient(45deg, ${entry.colors[0]} 50%, ${entry.colors[1]} 50%);"`;
              } else if (entry.color) {
                bg = ` style="background: ${entry.color};"`;
              }
            }
            const cls = isWeekend ? 'weekend' : '';
            const hl = entry ? ' highlighted' : '';
            html += `<td class="${cls}${hl}" data-date="${dateStr}" data-day-index="${day}" onclick="MSTPlanner.toggle(this)">${day}</td>`;
            day++;
          } else {
            html += '<td class="empty"></td>';
          }
        }
        html += '</tr>';
      }

      html += '</tbody></table></div>';
      container.insertAdjacentHTML('beforeend', html);
    }

    updateCounts();
  }

  // ----- Toggle day highlight -----
  function toggle(td) {
    const dateStr = td.dataset.date;
    if (!dateStr) return;

    let entry = savedData.find(e => e.date === dateStr);

    if (entry) {
      // Remove highlight
      savedData = savedData.filter(e => e.date !== dateStr);
      td.classList.remove('highlighted');
      td.style.background = '';
      td.style.border = '';
    } else {
      entry = { date: dateStr };
      if (twoColorMode && selectedColor && secondColor) {
        entry.colors = [selectedColor, secondColor];
      } else {
        entry.color = selectedColor;
      }
      savedData.push(entry);
      td.classList.add('highlighted');
      applyEntryStyle(td, entry);
    }

    persist();
    updateCounts();
  }

  function applyEntryStyle(td, entry) {
    if (entry.colors && entry.colors.length >= 2) {
      td.style.background = `linear-gradient(45deg, ${entry.colors[0]} 50%, ${entry.colors[1]} 50%)`;
    } else if (entry.color) {
      td.style.background = entry.color;
    }
    td.style.border = '1px solid var(--border-light)';
  }

  // ----- Update counters -----
  function updateCounts() {
    let weekdaysOff = 0;
    let totalOff = 0;

    document.querySelectorAll('#calendar-container td[data-date]').forEach(td => {
      if (td.classList.contains('highlighted')) {
        const dayIndex = parseInt(td.dataset.dayIndex);
        totalOff++;
        if (dayIndex % 7 !== 0 && dayIndex % 7 !== 6) weekdaysOff++; // Mon-Fri check
      }
    });

    const daysEl = el('#days-off-count');
    const totalEl = el('#total-off-count');
    if (daysEl) daysEl.textContent = weekdaysOff;
    if (totalEl) totalEl.textContent = totalOff;
  }

  // ----- Color selection -----
  function setColor(color) {
    if (twoColorMode) {
      if (!selectedColor) {
        selectedColor = color;
      } else if (!secondColor && color !== selectedColor) {
        secondColor = color;
      } else if (selectedColor === color) {
        selectedColor = secondColor;
        secondColor = null;
      } else if (secondColor === color) {
        secondColor = null;
      } else {
        selectedColor = color;
        secondColor = null;
      }
    } else {
      selectedColor = color;
      secondColor = null;
    }
    updateColorUI();
  }

  function updateColorUI() {
    document.querySelectorAll('.color-btn').forEach(btn => {
      const c = btn.dataset.color;
      const active = twoColorMode
        ? (c === selectedColor || c === secondColor)
        : (c === selectedColor);
      btn.classList.toggle('selected', !!active);
    });

    const preview = el('#color-preview');
    if (preview) {
      if (twoColorMode && selectedColor && secondColor) {
        preview.style.background = `linear-gradient(45deg, ${selectedColor} 50%, ${secondColor} 50%)`;
      } else {
        preview.style.background = selectedColor || '#000000';
      }
    }
  }

  function toggleTwoColor(checkbox) {
    twoColorMode = checkbox.checked;
    if (!twoColorMode) secondColor = null;
    updateColorUI();
  }

  function addCustomColor() {
    const input = document.createElement('input');
    input.type = 'color';
    input.onchange = () => {
      const newColor = input.value;
      const exists = document.querySelector(`.color-btn[data-color="${newColor}"]`);
      if (!exists) {
        const wrap = el('#color-buttons');
        if (wrap) wrap.insertAdjacentHTML('beforeend', colorBtnHTML(newColor));
        // Re-bind
        bindColorButtons();
      }
      setColor(newColor);
    };
    input.click();
  }

  function resetDefaults() {
    document.querySelectorAll('.color-btn').forEach(btn => btn.remove());
    const wrap = el('#color-buttons');
    if (wrap) {
      wrap.innerHTML = DEFAULT_COLORS.map(colorBtnHTML).join('');
    }
    selectedColor = '#ff0000';
    secondColor = null;
    twoColorMode = false;
    const checkbox = el('#two-color-checkbox');
    if (checkbox) checkbox.checked = false;
    bindColorButtons();
    updateColorUI();
  }

  function clearAll() {
    savedData = [];
    persist();
    renderCalendar();
    if (window.MST) MST.showToast('cleared highlights', 'success');
  }

  function bindColorButtons() {
    document.querySelectorAll('.color-btn').forEach(btn => {
      btn.addEventListener('click', () => setColor(btn.dataset.color));
    });
    updateColorUI();
  }

  function changeYear(year) {
    currentYear = parseInt(year);
    renderCalendar();
  }

  // ----- Export/Import (visual only, no file download needed) -----
  function exportData() {
    // Show a toast with count (no actual download per user request)
    if (window.MST) {
      const count = savedData.length;
      MST.showToast(count > 0 ? `${count} days highlighted (saved in this browser)` : 'no highlights yet', count > 0 ? 'success' : 'info');
    }
  }

  // ----- Init -----
  function init() {
    if (!el('#calendar-app')) return;

    loadSaved();

    // Year select
    const yearWrap = el('#year-select-wrap');
    if (yearWrap) {
      let optionsHtml = '';
      for (let y = currentYear - 5; y <= currentYear + 5; y++) {
        optionsHtml += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
      }
      yearWrap.innerHTML = `<select id="year-select" aria-label="Select year">${optionsHtml}</select>`;
      el('#year-select').addEventListener('change', e => changeYear(e.target.value));
    }

    // Color buttons
    const colorWrap = el('#color-buttons');
    if (colorWrap) {
      colorWrap.innerHTML = DEFAULT_COLORS.map(colorBtnHTML).join('');
    }
    bindColorButtons();

    // Two-color checkbox
    const checkbox = el('#two-color-checkbox');
    if (checkbox) checkbox.addEventListener('change', e => toggleTwoColor(e.target));

    // Toolbar buttons
    const customBtn = el('#pick-color-btn');
    if (customBtn) customBtn.addEventListener('click', addCustomColor);

    const defaultBtn = el('#default-btn');
    if (defaultBtn) defaultBtn.addEventListener('click', resetDefaults);

    const clearBtn = el('#clear-btn');
    if (clearBtn) clearBtn.addEventListener('click', clearAll);

    const exportBtn = el('#export-btn');
    if (exportBtn) exportBtn.addEventListener('click', exportData);

    renderCalendar();
    updateColorUI();
  }

  document.addEventListener('DOMContentLoaded', init);

  // Expose for inline onclick handlers
  window.MSTPlanner = {
    toggle,
    changeYear,
    setColor,
    addCustomColor,
    resetDefaults,
    clearAll,
    exportData
  };
})();