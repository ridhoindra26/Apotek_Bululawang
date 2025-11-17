import Swal from 'sweetalert2'
// import 'sweetalert2/dist/sweetalert2.min.css'

/* ---------- helpers ---------- */
const POSITIVE_TYPES = new Set(['overtime_add', 'penalty_reduce','initial_balance'])
const isMobile = () => window.matchMedia('(max-width: 640px)').matches
const cap = (s='') => s ? s.charAt(0).toUpperCase() + s.slice(1) : s
const humanType = (t='') => cap(t.replaceAll('_', ' '))

function pillStyle(type) {
  if (POSITIVE_TYPES.has(type)) {
    // emerald
    return 'background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;'
  }
  // rose
  return 'background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;'
}
function minutesText(type, minutes) {
  const m = Math.max(0, parseInt(minutes || 0, 10))
  const sign = POSITIVE_TYPES.has(type) ? '+' : '-'
  return `${sign}${m} min`
}
function minutesStyle(type) {
  return POSITIVE_TYPES.has(type)
    ? 'color:#047857;font-weight:600;'
    : 'color:#b91c1c;font-weight:600;'
}

/* ---------- desktop table ---------- */
function tableHtml(items) {
  return `
  <div style="max-height:65vh;overflow:auto">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="text-align:left;color:#64748b;border-bottom:1px solid #e2e8f0">
          <th style="padding:8px 12px">Tanggal</th>
          <th style="padding:8px 12px">Tipe</th>
          <th style="padding:8px 12px">Menit</th>
          <th style="padding:8px 12px">Sumber</th>
          <th style="padding:8px 12px">Catatan</th>
        </tr>
      </thead>
      <tbody>
        ${
          items.length
          ? items.map(row => {
              const date = row.date || '—'
              const type = row.type || ''
              const src  = row.source || '—'
              const noteFull = row.note || '—'
              const noteShort = noteFull.length > 200 ? noteFull.slice(0, 200) + '…' : noteFull
              const needsMore = noteFull.length > 200

              return `
                <tr style="border-bottom:1px solid #e2e8f0">
                  <td style="padding:10px 12px">${date}</td>
                  <td style="padding:10px 12px">
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:12px;border:1px solid;${pillStyle(type)}">
                      ${humanType(type)}
                    </span>
                  </td>
                  <td style="padding:10px 12px;${minutesStyle(type)}">${minutesText(type, row.minutes)}</td>
                  <td style="padding:10px 12px;color:#475569;text-transform:capitalize">${src}</td>
                  <td style="padding:10px 12px;max-width:260px">
                    <div class="swal-note" title="${escapeHtml(noteFull)}" data-full="${escapeHtmlAttr(noteFull)}">
                      <span class="swal-note-text" style="color:#64748b">${escapeHtml(noteShort)}</span>
                      ${needsMore
                        ? `<div style="margin-top:6px">
                            <button type="button" class="swal-more-btn"
                              style="font-size:12px;border:1px solid #e2e8f0;border-radius:8px;padding:4px 8px;background:#fff">
                              Show more
                            </button>
                           </div>`
                        : ''}
                    </div>
                  </td>
                </tr>
              `
            }).join('')
          : `<tr><td colspan="5" style="padding:14px;text-align:center;color:#94a3b8">No ledger records found.</td></tr>`
        }
      </tbody>
    </table>
  </div>`
}

/* ---------- mobile cards ---------- */
function cardsHtml(items) {
  if (!items.length) {
    return `<div style="padding:12px;text-align:center;color:#94a3b8">No ledger records found.</div>`
  }
  const card = r => {
    const type = r.type || ''
    const src  = r.source || '—'
    const noteFull = r.note || '—'
    const noteShort = noteFull.length > 200 ? noteFull.slice(0, 200) + '…' : noteFull
    const needsMore = noteFull.length > 200

    return `
      <div style="border:1px solid #e2e8f0;border-radius:12px;padding:12px;margin-bottom:10px;background:#fff">
        <div style="font-weight:600;color:#0f172a;margin-bottom:6px">${r.date || '—'}</div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
          <span style="display:inline-flex;align-items:center;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700;${pillStyle(type)}">
            ${humanType(type)}
          </span>
          <span style="margin-left:auto;${minutesStyle(type)}">${minutesText(type, r.minutes)}</span>
        </div>

        <div style="display:grid;grid-template-columns:90px 1fr;gap:6px;font-size:13px;color:#475569">
          <div style="color:#64748b">Sumber</div><div style="text-transform:capitalize">${src}</div>
          <div style="color:#64748b">Catatan</div>
          <div class="swal-note" data-full="${escapeHtmlAttr(noteFull)}">
            <span class="swal-note-text" style="color:#64748b">${escapeHtml(noteShort)}</span>
            ${needsMore
              ? `<div style="margin-top:6px">
                   <button type="button" class="swal-more-btn"
                     style="font-size:12px;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;background:#fff;width:100%">
                     Show more
                   </button>
                 </div>`
              : ''}
          </div>
        </div>
      </div>
    `
  }
  return `<div style="max-height:70vh;overflow:auto;padding-bottom:4px">${items.map(card).join('')}</div>`
}

/* ---------- note expansion (event delegation) ---------- */
function bindShowMore(container) {
  container.addEventListener('click', (e) => {
    const btn = e.target.closest('.swal-more-btn')
    if (!btn) return
    const wrap = btn.closest('.swal-note')
    if (!wrap) return

    const full = wrap.getAttribute('data-full') || ''
    const textEl = wrap.querySelector('.swal-note-text')
    if (textEl) {
      textEl.textContent = decodeHtml(full)
    }
    btn.remove() // remove the button after expansion
  })
}

/* ---------- main open ---------- */
async function openLedger(endpoint) {
  // quick loading
  Swal.fire({
    title: 'Time Ledger',
    html: '<div style="padding:8px 0;color:#64748b;font-size:14px">Loading…</div>',
    showConfirmButton: false,
    showCloseButton: true,
    didOpen: () => Swal.showLoading(),
    width: isMobile() ? '100%' : '900px',
    padding: isMobile() ? '0' : undefined,
    customClass: { popup: isMobile() ? 'swal2-mobile-fullscreen' : '' },
  })

  try {
    const res = await fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    if (!res.ok) throw new Error('Failed to fetch ledger')
    const items = await res.json()

    const html = isMobile() ? cardsHtml(items) : tableHtml(items)

    Swal.fire({
      title: 'Time Ledger',
      html,
      width: isMobile() ? '100%' : '900px',
      showCloseButton: true,
      confirmButtonText: 'Tutup',
      focusConfirm: false,
      padding: isMobile() ? '12px' : undefined,
      customClass: { popup: isMobile() ? 'swal2-mobile-fullscreen' : '' },
      didOpen: () => {
        // remove outer padding to avoid right gutter on mobile
        const cont = document.querySelector('.swal2-container')
        if (cont) cont.style.padding = '0'
        const htmlBox = document.querySelector('.swal2-html-container')
        if (htmlBox) bindShowMore(htmlBox)
      }
    })
  } catch (e) {
    Swal.fire('Gagal', 'Gagal mengambil ledger.', 'error')
  }
}

/* ---------- init & utilities ---------- */
function injectOnceMobileCss() {
  if (document.getElementById('swal2-mobile-css')) return
  const style = document.createElement('style')
  style.id = 'swal2-mobile-css'
  style.textContent = `
    .swal2-mobile-fullscreen {
      width: 100% !important;
      max-width: 100% !important;
      height: 100%;
      margin: 0 !important;
      border-radius: 0 !important;
    }
    .swal2-title {
      font-size: 18px !important;
      line-height: 1.2;
      padding: 12px 16px 0 16px;
      text-align: left !important;
    }
    .swal2-html-container { margin: 10px 16px 8px 16px !important; }
    .swal2-actions { padding: 8px 16px 14px 16px; }
    .swal2-actions .swal2-confirm { width: 100%; border-radius: 10px; padding: 10px 12px; font-weight: 700; }
    .swal2-close { top: 10px !important; right: 10px !important; }
  `
  document.head.appendChild(style)
}

function initLedgerButtons() {
  injectOnceMobileCss()

  const bind = () => {
    document.querySelectorAll('[data-ledger-trigger]').forEach(btn => {
      if (btn._ledgerBound) return
      btn._ledgerBound = true
      btn.addEventListener('click', () => {
        const endpoint = btn.getAttribute('data-ledger-endpoint')
        if (endpoint) openLedger(endpoint)
      }, { passive: true })
    })
  }

  bind()
  document.addEventListener('apotek:rebinding', bind)
  document.addEventListener('DOMContentLoaded', bind)
}

/* ---- minimal HTML escaping helpers ---- */
function escapeHtml(s='') {
  return s.replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))
}
function escapeHtmlAttr(s='') {
  return escapeHtml(s).replace(/"/g, '&quot;')
}
function decodeHtml(s='') {
  const el = document.createElement('textarea')
  el.innerHTML = s
  return el.value
}

initLedgerButtons()
