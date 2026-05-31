// ── Donations page ─────────────────────────────────────────────────────────────
const condLabel = { new:'جديد', good:'جيد', fair:'مقبول' };
const condBadge = { new:'badge-secondary cond-badge', good:'badge-primary cond-badge', fair:'badge-orange cond-badge' };
const catEmoji  = { medicine:'💊', device:'🩺', special_needs:'♿', parapharmacy:'🧴', other:'🎁' };

async function loadDonations(wilaya = '') {
  const grid = document.getElementById('donations-grid');
  const countEl = document.getElementById('donations-count');
  grid.innerHTML = '<div class="spinner" style="margin:2rem auto"></div>';
  try {
    const data = await api.get('/donations/index.php', wilaya ? { wilaya } : {});
    countEl.textContent = `${formatNum(data.total)} تبرع`;
    if (!data.donations.length) {
      grid.innerHTML = '<div class="empty-state"><div class="empty-icon">🎁</div><p>لا توجد تبرعات حالياً</p></div>';
      return;
    }
    grid.innerHTML = data.donations.map((d, i) => `
      <div class="donation-full-card animate-up anim-delay-${(i%4)+1}">
        <div class="donation-emoji">${catEmoji[d.category] || '🎁'}</div>
        <div class="donation-body">
          <h3>${d.item_name_ar}</h3>
          ${d.description ? `<p>${d.description}</p>` : ''}
          <div class="donation-tags">
            <span class="badge ${condBadge[d.condition] || 'badge-muted'}">${condLabel[d.condition] || d.condition}</span>
            <span class="badge badge-muted">📍 ${d.city}، ${d.wilaya}</span>
          </div>
          <div class="donation-footer">
            <span class="donation-donor">👤 ${d.donor_name}</span>
            <span style="font-size:0.75rem;color:var(--muted)">${formatDate(d.created_at)}</span>
          </div>
        </div>
      </div>`).join('');
  } catch {
    grid.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--muted)">تعذر التحميل</p>';
  }
}

async function submitDonation(e) {
  e.preventDefault();
  const btn = e.target.querySelector('[type=submit]');
  btn.disabled = true; btn.textContent = 'جارٍ الإضافة...';
  try {
    const formData = new FormData();
    formData.append('item_name',    document.getElementById('d-name-en').value);
    formData.append('item_name_ar', document.getElementById('d-name-ar').value);
    formData.append('description',  document.getElementById('d-desc').value);
    formData.append('wilaya',       document.getElementById('d-wilaya').value);
    formData.append('city',         document.getElementById('d-city').value);
    formData.append('donor_name',   document.getElementById('d-donor').value);
    formData.append('condition',    document.getElementById('d-condition').value);
    formData.append('category',     document.getElementById('d-category').value);
    const imgInput = document.getElementById('d-image');
    if (imgInput && imgInput.files[0]) formData.append('image', imgInput.files[0]);

    const res = await fetch(`${API_BASE}/donations/create.php`, { method: 'POST', body: formData });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');

    closeModal('donation-modal');
    showToast('تم إضافة التبرع بنجاح', 'success');
    e.target.reset();
    document.getElementById('d-img-preview').style.display = 'none';
    loadDonations(document.getElementById('wilaya-filter-d')?.value || '');
  } catch (err) {
    console.error('[Donations] submit failed:', err);
    showToast('حدث خطأ أثناء الإضافة', 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'إضافة التبرع';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const wSel = document.getElementById('wilaya-filter-d');
  const wSelForm = document.getElementById('d-wilaya');
  [wSel, wSelForm].forEach(s => {
    if (!s) return;
    WILAYAS.forEach(w => { const o = document.createElement('option'); o.value = w; o.textContent = w; s.appendChild(o); });
  });
  if (wSel) wSel.addEventListener('change', () => loadDonations(wSel.value));
  document.getElementById('donation-form')?.addEventListener('submit', submitDonation);

  // Image preview
  document.getElementById('d-image')?.addEventListener('change', e => {
    const file = e.target.files[0];
    const preview = document.getElementById('d-img-preview');
    const previewSrc = document.getElementById('d-img-preview-src');
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = ev => { previewSrc.src = ev.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(file);
    } else {
      preview.style.display = 'none';
    }
  });

  loadDonations();
});
