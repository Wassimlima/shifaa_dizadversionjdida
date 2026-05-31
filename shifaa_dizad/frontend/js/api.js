// ── Fetch Utility ────────────────────────────────────────────────────────────
async function apiFetch(path, options = {}) {
  const url = `${API_BASE}${path}`;

  // Only send Content-Type for requests that have a body (POST, PUT, PATCH)
  const hasBody = options.body !== undefined;
  const defaultHeaders = hasBody ? { 'Content-Type': 'application/json' } : {};

  try {
    const res = await fetch(url, {
      headers: { ...defaultHeaders, ...options.headers },
      ...options,
    });

    // Try to parse JSON regardless of status so we can surface the error message
    let data;
    try {
      data = await res.json();
    } catch {
      throw new Error(`HTTP ${res.status} — réponse non-JSON`);
    }

    if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
    return data;
  } catch (err) {
    console.error('[API]', path, err.message);
    throw err;
  }
}

const api = {
  get: (path, params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return apiFetch(qs ? `${path}?${qs}` : path);
  },
  post:   (path, body) => apiFetch(path, { method: 'POST',   body: JSON.stringify(body) }),
  put:    (path, body) => apiFetch(path, { method: 'PUT',    body: JSON.stringify(body) }),
  patch:  (path, body) => apiFetch(path, { method: 'PATCH',  body: JSON.stringify(body) }),
  delete: (path)       => apiFetch(path, { method: 'DELETE' }),
};
