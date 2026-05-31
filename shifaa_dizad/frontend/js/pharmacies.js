// ── Pharmacies page ───────────────────────────────────────────────────────────
async function loadPharmacies(wilaya = '') {
  const grid = document.getElementById('pharmacies-grid');
  const countEl = document.getElementById('pharmacies-count');
  grid.innerHTML = '<div class="spinner" style="margin:2rem auto;grid-column:1/-1"></div>';
  try {
    const data = await api.get('/pharmacies/index.php', { wilaya, limit: 20 });
    countEl.textContent = `${formatNum(data.total)} صيدلية`;
    if (!data.pharmacies.length) {
      grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><div class="empty-icon">🏥</div><p>لا توجد صيدليات في هذه الولاية</p></div>';
      return;
    }
    const planLabel = { free:'مجاني', professional:'احترافي', enterprise:'مؤسسي' };
    const planClass = { free:'badge-muted', professional:'badge-primary', enterprise:'badge-secondary' };
    grid.innerHTML = data.pharmacies.map((p, i) => `
      <div class="pharmacy-card animate-up anim-delay-${(i%4)+1}">
        <div class="pharmacy-header">
          <div style="display:flex;gap:0.75rem;align-items:flex-start">
            <div class="pharmacy-icon">🏥</div>
            <div>
              <div class="pharmacy-name">${p.name}</div>
              <div class="pharmacy-city">📍 ${p.city}، ${p.wilaya}</div>
            </div>
          </div>
          <div class="pharmacy-rating">⭐ ${Number(p.rating).toFixed(1)} <span style="color:var(--muted);font-weight:400">(${p.review_count})</span></div>
        </div>
        <div class="pharmacy-info">
          <span>📞 ${p.phone}</span>
          <span>🕐 ${p.opening_hours || '08:00 - 22:00'}</span>
          <span>📌 ${p.address}</span>
        </div>
        <div class="pharmacy-footer">
          <span class="open-badge ${p.is_open ? 'open' : 'closed'}">
            ${p.is_open ? '● مفتوح' : '● مغلق'}
          </span>
          <span class="badge ${planClass[p.plan] || 'badge-muted'} plan-badge">${planLabel[p.plan] || p.plan}</span>
        </div>
      </div>`).join('');
  } catch {
    grid.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--muted);grid-column:1/-1">تعذر التحميل</p>';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const wSel = document.getElementById('wilaya-filter');
  WILAYAS.forEach(w => { const o = document.createElement('option'); o.value = w; o.textContent = w; wSel.appendChild(o); });
  wSel.addEventListener('change', () => loadPharmacies(wSel.value));
  loadPharmacies();
});
