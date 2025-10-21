import Swal from "sweetalert2";

// === inject CSS sekali saja untuk styling Swal responsif ===
(function injectPhotoModalCSS() {
  if (document.getElementById('photo-modal-css')) return;
  const style = document.createElement('style');
  style.id = 'photo-modal-css';
  style.textContent = `
    .swal2-popup.photo-modal { padding: 0; border-radius: 1rem; }
    .swal2-title.photo-title { margin: .5rem .75rem 0; font-size: .9rem; color: #334155; }
    .swal2-html-container.photo-body { margin: .5rem; }
    .swal2-actions.photo-actions { margin: .5rem .75rem .75rem; }
    .swal-photo {
      display: block;
      margin: auto;
      width: auto; height: auto;
      max-width: 100%;
      max-height: 78vh;        /* tinggi aman di mobile */
      border-radius: .75rem;
      object-fit: contain;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
  `;
  document.head.appendChild(style);
})();

// Helper: preload image biar ukuran pas & error-nya rapi
async function preloadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve({ width: img.naturalWidth, height: img.naturalHeight, src });
    img.onerror = () => reject(new Error('Failed to load image'));
    img.src = src;
  });
}

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.js-photo-fetch');
  if (!btn) return;

  const apiUrl  = btn.getAttribute('data-url');     // route('attendance.photo', ...)
  const caption = btn.getAttribute('data-caption') || 'Attendance Photo';

  // Loading awal
  Swal.fire({
    title: 'Loading photo...',
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
    showConfirmButton: false,
  });

  try {
    // ambil JSON { img: "public-url" }
    const res = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (!res.ok || !data?.img) throw new Error(data?.message || 'Photo not found.');

    // preload untuk hitung rasio & pastikan bisa diload
    const pre = await preloadImage(data.img);

    // tentukan lebar popup responsif:
    // - mobile: 94vw, tablet: 90vw, desktop: max 720px
    const w = window.innerWidth;
    const popupWidth = w < 480 ? '94vw' : (w < 768 ? '90vw' : '720px');

    Swal.fire({
      title: caption,
      html: `<img class="swal-photo" src="${pre.src}" alt="Attendance Photo">`,
      showCloseButton: true,
      showConfirmButton: true,
      confirmButtonText: 'Download',
      confirmButtonColor: '#318f8c',
      width: popupWidth,
      background: '#fff',
      allowOutsideClick: true,
      customClass: {
        popup: 'photo-modal',
        title: 'photo-title',
        htmlContainer: 'photo-body',
        actions: 'photo-actions',
      },
    }).then((result) => {
      if (result.isConfirmed) {
        const a = document.createElement('a');
        a.href = pre.src;
        a.download = caption.replace(/[^\w\-]+/g, '_');
        a.click();
      }
    });

  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Photo not found',
      text: err.message || 'Cannot load photo',
      confirmButtonColor: '#318f8c'
    });
  }
});
