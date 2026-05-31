// ── Labs page ──────────────────────────────────────────────────────────────────
const labParams = new URLSearchParams(window.location.search);
let currentLabCat = '';

const catLabels = {
  hematologie: 'أمراض الدم',
  biochimie:   'بيوكيمياء',
  microbiologie:'ميكروبيولوجيا',
  hormonologie: 'هرمونات',
  immunologie:  'مناعة',
  general:      'عام',
};

function getLabFilters() {
  return {
    q:        document.getElementById('lab-query')?.value.trim() || '',
    wilaya:   document.getElementById('lab-wilaya')?.value || '',
    category: currentLabCat,
  };
}

async function doLabSearch() {
  const container = document.getElementById('lab-results');
  const countEl   = document.getElementById('lab-results-count');
  if (!container) return;

  container.innerHTML = '<div class="spinner" style="margin:2rem auto"></div>';

  try {
    const f = getLabFilters();
    console.log('[Labs] Searching with filters:', f);
    const data = await api.get('/labs/index.php', f);
    console.log('[Labs] Response:', data);

    const analyses = data.analyses ?? [];
    const total    = data.total ?? analyses.length;
    if (countEl) countEl.textContent = `${formatNum(total)} نتيجة`;

    if (!analyses.length) {
      container.innerHTML = `<div class="empty-state">
        <div class="empty-icon">🧪</div>
        <p>لا توجد نتائج مطابقة</p>
      </div>`;
      return;
    }

    container.innerHTML = analyses.map(a => `
      <div class="lab-card animate-in">
        <div class="lab-icon">🧪</div>
        <div class="lab-info">
          <div class="analysis-name">${a.name}</div>
          <div class="analysis-name-ar">${a.name_ar}</div>
          <div class="lab-name" style="margin-top:0.4rem">🏥 ${a.lab_name_ar || a.lab_name}</div>
          <div class="lab-meta">
            <span>📍 ${a.city}، ${a.wilaya}</span>
            <span>📞 ${a.phone}</span>
            <span class="${a.is_open == 1 ? 'status-open' : 'status-closed'}">${a.is_open == 1 ? '● مفتوح' : '● مغلق'}</span>
            <span>🕐 ${a.opening_hours}</span>
            ${a.maps_link ? `<a href="${a.maps_link}" target="_blank" style="color:var(--primary);text-decoration:none">🗺️ خريطة</a>` : ''}
          </div>
          ${a.description ? `<div style="font-size:0.8rem;color:var(--muted);margin-top:0.35rem">${a.description}</div>` : ''}
          <div style="margin-top:0.4rem">
            <span class="badge badge-muted" style="font-size:0.72rem">${catLabels[a.category] || a.category}</span>
          </div>
        </div>
        <div class="lab-right">
          <div>
            <div class="price-tag">${formatNum(a.price)}</div>
            <div class="price-label">دج</div>
          </div>
          <div class="prep-time">⏱ ${a.preparation_time}</div>
          <div style="font-size:0.8rem;color:var(--muted)">⭐ ${parseFloat(a.rating).toFixed(1)}</div>
        </div>
      </div>`).join('');
  } catch (err) {
    console.error('[Labs] Search failed:', err);
    container.innerHTML = `<p style="color:var(--muted);text-align:center;padding:2rem">حدث خطأ أثناء البحث</p>`;
  }
}

function initLabs() {
  const qEl = document.getElementById('lab-query');
  const wEl = document.getElementById('lab-wilaya');

  if (qEl && labParams.get('q')) qEl.value = labParams.get('q');

  if (wEl && typeof WILAYAS !== 'undefined') {
    WILAYAS.forEach(w => {
      const o = document.createElement('option');
      o.value = w; o.textContent = w;
      wEl.appendChild(o);
    });
  }

  document.getElementById('lab-search-form')?.addEventListener('submit', e => {
    e.preventDefault();
    doLabSearch();
  });

  document.getElementById('lab-wilaya')?.addEventListener('change', doLabSearch);

  document.querySelectorAll('#lab-cat-filters .cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#lab-cat-filters .cat-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentLabCat = btn.dataset.cat;
      doLabSearch();
    });
  });

  doLabSearch();
}

document.addEventListener('DOMContentLoaded', initLabs);
