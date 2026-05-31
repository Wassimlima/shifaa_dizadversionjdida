// ── Prescription upload page ───────────────────────────────────────────────────
const DEMO_MEDICINES = [
  { name: 'Doliprane 1000mg', active: 'Paracetamol', available: true },
  { name: 'Ventoline 100mcg', active: 'Salbutamol', available: false },
  { name: 'Amoxicilline 500mg', active: 'Amoxicillin', available: true },
];

function initPrescription() {
  const zone     = document.getElementById('rx-upload-zone');
  const fileInput= document.getElementById('rx-file-input');
  const preview  = document.getElementById('rx-preview');
  const previewImg = document.getElementById('rx-preview-img');
  const fileName = document.getElementById('rx-file-name');
  const form     = document.getElementById('rx-form');

  if (!zone) return;

  fileInput?.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    if (fileName) fileName.textContent = file.name;
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = ev => {
        previewImg.src = ev.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      if (previewImg) previewImg.src = '';
      if (preview) preview.style.display = 'block';
      if (previewImg) { previewImg.style.display = 'none'; }
    }
  });

  zone?.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone?.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone?.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && fileInput) {
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
      fileInput.dispatchEvent(new Event('change'));
    }
  });

  form?.addEventListener('submit', async e => {
    e.preventDefault();
    const file = fileInput?.files[0];
    const btn  = document.getElementById('rx-submit-btn');

    if (!file) {
      showToast('يرجى رفع صورة الوصفة الطبية أولاً', 'error');
      return;
    }

    btn.disabled = true;
    btn.textContent = '⏳ جارٍ التحليل...';

    try {
      const formData = new FormData();
      formData.append('prescription', file);
      formData.append('patientName', document.getElementById('rx-patient-name')?.value || '');
      formData.append('notes', document.getElementById('rx-notes')?.value || '');

      const res = await fetch(`${API_BASE}/prescriptions/upload.php`, { method: 'POST', body: formData });
      const data = await res.json().catch(() => ({}));
      console.log('[Prescription] Upload response:', data);
    } catch (err) {
      console.warn('[Prescription] Upload failed (expected in demo):', err);
    }

    await new Promise(r => setTimeout(r, 1800));
    showDemoResults();

    btn.disabled = false;
    btn.textContent = 'تحليل الوصفة بالذكاء الاصطناعي 🤖';
  });
}

function showDemoResults() {
  const box  = document.getElementById('rx-ai-result');
  const list = document.getElementById('rx-ai-list');
  if (!box || !list) return;

  list.innerHTML = DEMO_MEDICINES.map(m => `
    <li>
      <div>
        <div style="font-weight:700">${m.name}</div>
        <div style="font-size:0.78rem;color:var(--muted)">${m.active}</div>
      </div>
      <div style="display:flex;gap:0.5rem;align-items:center">
        ${m.available
          ? `<span class="badge badge-secondary">متوفر</span>
             <a href="search.html?q=${encodeURIComponent(m.name)}" class="btn btn-outline btn-sm">بحث</a>`
          : `<span class="badge badge-red">غير متوفر</span>`}
      </div>
    </li>`).join('');

  box.style.display = 'block';
  showToast('تم تحليل الوصفة (نتائج تجريبية)', 'success');
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

document.addEventListener('DOMContentLoaded', initPrescription);
