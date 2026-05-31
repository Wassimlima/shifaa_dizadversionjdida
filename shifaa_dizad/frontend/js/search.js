// ── Search page ───────────────────────────────────────────────────────────────

let currentPage = 1;

const params = new URLSearchParams(window.location.search);

const PRICE_TYPES = [
  'device',
  'parapharmacy',
  'special_needs',
  'home_care',
  'emergency'
];

// ──────────────────────────────────────────────────────────────────────────────
// Category Filter
// ──────────────────────────────────────────────────────────────────────────────

function setCatFilter(btn, type) {

  document
    .querySelectorAll('.cat-filter-btn, .sqf-btn')
    .forEach(b => b.classList.remove('active'));

  btn.classList.add('active');

  const sel = document.getElementById('s-type');

  if (sel) {
    sel.value = type;
  }

  currentPage = 1;

  doSearch();
}

// ──────────────────────────────────────────────────────────────────────────────
// Get Filters
// ──────────────────────────────────────────────────────────────────────────────

function getFilters() {

  return {

    q:
      document.getElementById('s-query')
      ?.value.trim() || '',

    wilaya:
      document.getElementById('s-wilaya')
      ?.value || '',

    type:
      document.getElementById('s-type')
      ?.value || '',

    availability:
      document.getElementById('s-availability')
      ?.value || '',

    page: currentPage,

    limit: 10
  };
}

// ──────────────────────────────────────────────────────────────────────────────
// Availability Badge
// ──────────────────────────────────────────────────────────────────────────────

function availabilityBadge(status) {

  if (status === 'available') {

    return `
      <span class="badge badge-secondary">
        متوفر
      </span>
    `;
  }

  if (status === 'limited') {

    return `
      <span class="badge badge-orange">
        محدود
      </span>
    `;
  }

  return `
    <span class="badge badge-red">
      غير متوفر
    </span>
  `;
}

// ──────────────────────────────────────────────────────────────────────────────
// Search Function
// ──────────────────────────────────────────────────────────────────────────────

async function doSearch() {

  const container =
    document.getElementById('results');

  const countEl =
    document.getElementById('results-count');

  if (!container) return;

  container.innerHTML = `
    <div class="spinner"
      style="margin:2rem auto">
    </div>
  `;

  try {

    const filters = getFilters();

    const response =
      await api.get(
        '/medicines/search.php',
        filters
      );

    console.log(
      '[Search API Response]',
      response
    );

    // ─────────────────────────────────────────
    // New API Structure Support
    // ─────────────────────────────────────────

    const medicines =

      response.data?.medicines ??

      response.medicines ??

      response.results ??

      [];

    const total =

      response.data?.total ??

      response.total ??

      medicines.length;

    // ─────────────────────────────────────────

    if (countEl) {

      countEl.textContent =
        `${formatNum(total)} نتيجة`;
    }

    const barCount = document.getElementById('results-count-bar');
    if (barCount) {
      barCount.textContent = `${formatNum(total)} نتيجة`;
    }

    // ─────────────────────────────────────────
    // Empty State
    // ─────────────────────────────────────────

    if (!medicines.length) {

      container.innerHTML = `

      <div class="empty-state">

        <div class="empty-icon">
          🔍
        </div>

        <h3>
          لا توجد نتائج
        </h3>

        <p>
          حاول تغيير كلمات البحث أو الفلاتر
        </p>

      </div>
      `;

      return;
    }

    // ─────────────────────────────────────────
    // Render Medicines
    // ─────────────────────────────────────────

    container.innerHTML = medicines.map(med => {

      const showPrice =
        PRICE_TYPES.includes(med.type)
        && med.price;

      return `

      <div class="search-med-card animate-in">

        <div class="med-main">

          <h3>

            ${med.name}

            ${med.name_ar
              ? `
              <small
                style="
                font-weight:400;
                color:var(--muted)">
                ${med.name_ar}
              </small>
              `
              : ''
            }

          </h3>

          <div class="med-meta">

            <span>
              💊
              ${med.active_ingredient || 'غير محدد'}
            </span>

            <span>
              📍
              ${med.city}، ${med.wilaya}
            </span>

            <span>
              🏥
              ${med.pharmacy_name || '—'}
            </span>

            ${med.pharmacy_phone
              ? `
              <span>
                📞 ${med.pharmacy_phone}
              </span>
              `
              : ''
            }

          </div>

        </div>

        <div class="med-right">

          <div style="text-align:center">

            <div
              style="
              font-size:1.2rem;
              font-weight:900">

              ${med.quantity}

            </div>

            <div
              style="
              font-size:0.72rem;
              color:var(--muted)">

              وحدة

            </div>

          </div>

          ${showPrice
            ? `
            <div style="text-align:center">

              <div
                style="
                font-size:1rem;
                font-weight:800;
                color:var(--primary)">

                ${formatNum(med.price)} DA

              </div>

            </div>
            `
            : ''
          }

          ${availabilityBadge(med.availability)}

          <button
            class="btn btn-primary btn-sm"

            onclick='openReservation(
              ${JSON.stringify(med)}
            )'>

            حجز

          </button>

        </div>

      </div>

      `;

    }).join('');

  } catch (err) {

    console.error(
      '[Search Error]',
      err
    );

    container.innerHTML = `

    <div class="empty-state">

      <div class="empty-icon">
        ⚠️
      </div>

      <h3>
        حدث خطأ أثناء البحث
      </h3>

      <p>
        حاول إعادة تحميل الصفحة
      </p>

    </div>
    `;
  }
}

// ──────────────────────────────────────────────────────────────────────────────
// Reservation Modal
// ──────────────────────────────────────────────────────────────────────────────

let _resData = null;

function openReservation(med) {

  _resData = med;

  const nameEl =
    document.getElementById(
      'res-medicine-name'
    );

  const pharmacyEl =
    document.getElementById(
      'res-pharmacy-name'
    );

  if (nameEl) {

    nameEl.textContent = med.name;
  }

  if (pharmacyEl) {

    pharmacyEl.textContent =
      med.pharmacy_name || '—';
  }

  openModal('reservation-modal');
}

// ──────────────────────────────────────────────────────────────────────────────
// Submit Reservation
// ──────────────────────────────────────────────────────────────────────────────

async function submitReservation(e) {

  e.preventDefault();

  if (!_resData) return;

  const btn =
    e.target.querySelector(
      '[type=submit]'
    );

  btn.disabled = true;

  btn.textContent =
    'جارٍ الحجز...';

  try {

    await api.post(
      '/reservations/create.php',
      {

        medicineId:
          parseInt(_resData.id),

        pharmacyId:
          parseInt(_resData.pharmacy_id),

        medicineName:
          _resData.name,

        pharmacyName:
          _resData.pharmacy_name || '',

        patientName:
          document.getElementById(
            'res-patient-name'
          ).value,

        patientPhone:
          document.getElementById(
            'res-patient-phone'
          ).value,

        quantity:
          parseInt(
            document.getElementById(
              'res-qty'
            ).value
          ) || 1,

        notes:
          document.getElementById(
            'res-notes'
          ).value
      }
    );

    closeModal(
      'reservation-modal'
    );

    showToast(
      'تم الحجز بنجاح!',
      'success'
    );

    e.target.reset();

  } catch (err) {

    console.error(
      '[Reservation Error]',
      err
    );

    showToast(
      'حدث خطأ أثناء الحجز',
      'error'
    );

  } finally {

    btn.disabled = false;

    btn.textContent =
      'تأكيد الحجز';
  }
}

// ──────────────────────────────────────────────────────────────────────────────
// Init Search
// ──────────────────────────────────────────────────────────────────────────────

function initSearch() {

  const qEl =
    document.getElementById('s-query');

  const wEl =
    document.getElementById('s-wilaya');

  const tEl =
    document.getElementById('s-type');

  const aEl =
    document.getElementById('s-availability');

  // URL Params

  if (qEl && params.get('q')) {

    qEl.value =
      params.get('q');
  }

  if (wEl && params.get('wilaya')) {

    wEl.value =
      params.get('wilaya');
  }

  if (tEl && params.get('type')) {

    tEl.value =
      params.get('type');
  }

  if (aEl && params.get('availability')) {

    aEl.value =
      params.get('availability');
  }

  // Populate Wilaya Select

  if (
    wEl &&
    typeof WILAYAS !== 'undefined'
  ) {

    WILAYAS.forEach(w => {

      const option =
        document.createElement('option');

      option.value = w;

      option.textContent = w;

      wEl.appendChild(option);
    });
  }

  // Form Submit

  document
    .getElementById('search-form')
    ?.addEventListener(
      'submit',
      (e) => {

        e.preventDefault();

        currentPage = 1;

        doSearch();
      }
    );

  // Filters Change

  [
    's-wilaya',
    's-type',
    's-availability'
  ].forEach(id => {

    document
      .getElementById(id)
      ?.addEventListener(
        'change',
        () => {

          currentPage = 1;

          doSearch();
        }
      );
  });

  // Reservation Form

  document
    .getElementById('res-form')
    ?.addEventListener(
      'submit',
      submitReservation
    );

  // Auto Search

  if (
    params.get('q') ||
    params.get('category')
  ) {

    doSearch();
  }
}

// ──────────────────────────────────────────────────────────────────────────────

document.addEventListener(
  'DOMContentLoaded',
  initSearch
);