import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

function initCashierConfirm() {
  const buttons = document.querySelectorAll('.btn-confirm-doc');
  if (!buttons.length) return; // kalau bukan di halaman list, keluar saja

  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const docId = btn.getAttribute('data-id');
      const currentStatus = btn.getAttribute('data-status') || 'pending';
      const currentNote = btn.getAttribute('data-note') || '';
      const label = btn.getAttribute('data-label') || 'Dokumen kasir';

      const { value: formValues } = await Swal.fire({
        title: 'Konfirmasi Dokumen',
        html: `
          <div style="text-align:center;width:100%;margin-bottom:6px;color:#4b5563;">
            ${label}
          </div>
          <div style="text-align:center;margin-bottom:8px;width:100%;">
            <label style="display:block;font-size:11px;margin-bottom:2px;color:#4b5563;">Status</label>
            <select id="swal-status" class="swal2-input" style="height:auto;padding:.35rem .5rem;font-size:12px;width:100%;">
              <option value="pending"   ${currentStatus === 'pending' ? 'selected' : ''}>Menunggu</option>
              <option value="confirmed" ${currentStatus === 'confirmed' ? 'selected' : ''}>Terkonfirmasi</option>
              <option value="rejected"  ${currentStatus === 'rejected' ? 'selected' : ''}>Ditolak</option>
            </select>
          </div>
          <div style="text-align:center;width:100%;">
            <label style="display:block;font-size:11px;margin-bottom:2px;color:#4b5563;">Catatan Admin</label>
            <textarea id="swal-note" class="swal2-textarea" style="font-size:12px;height:80px;width:100%;margin: 0;">${currentNote}</textarea>
          </div>
        `,
        focusConfirm: true,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#318f8c',
        cancelButtonColor: 'red',
        customClass: {
          popup: 'rounded-2xl',
        //   confirmButton: 'rounded px-4 py-2 text-xs',
        //   cancelButton: 'rounded px-4 py-2 text-xs',
        },
        preConfirm: () => {
          const statusEl = document.getElementById('swal-status');
          const noteEl = document.getElementById('swal-note');

          if (!statusEl) return null;

          return {
            status: statusEl.value,
            note: noteEl.value || '',
          };
        },
      });

      if (!formValues) {
        return; 
      }

      const form = document.getElementById(`confirm-form-${docId}`);
      if (!form) return;

      form.querySelector('input[name="status"]').value = formValues.status;
      form.querySelector('input[name="admin_note"]').value = formValues.note;

      form.submit();
    });
  });
}

function initCashierEdit() {
  const buttons = document.querySelectorAll('.btn-edit-doc');
  if (!buttons.length) return;

  const csrf = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const description = btn.getAttribute('data-description') || '';
      const label = btn.getAttribute('data-label') || 'Dokumen kasir';
      const updateUrl = btn.getAttribute('data-update-url');
      if (!updateUrl) return;

      let photoItems = [];
      try {
        const raw = btn.getAttribute('data-photo-items') || '[]';
        photoItems = JSON.parse(raw) || [];
      } catch (e) {
        photoItems = [];
      }

      let deleteIds = new Set();
      let objectUrls = []; // preview URL untuk foto baru

      const existingHtml = photoItems.length
        ? `
          <div style="font-size:11px;color:#6b7280;text-align:left;margin-bottom:4px;">
            Klik foto untuk menandai <span style="color:#dc2626;font-weight:500;">hapus</span>.
          </div>
          <div id="swal-existing-photos"
               style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:8px;">
            ${photoItems
              .map(
                (p) => `
                  <div data-photo-id="${p.id}"
                       style="position:relative;cursor:pointer;">
                    <img src="${p.url}"
                         alt="Foto dokumen"
                         style="width:72px;height:72px;border-radius:0.75rem;border:1px solid #e5e7eb;object-fit:cover;">
                    <span
                      style="position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:999px;
                             background:#00000080;color:#ffffff;font-size:11px;display:flex;align-items:center;
                             justify-content:center;">
                      ×
                    </span>
                  </div>
                `
              )
              .join('')}
          </div>
        `
        : `
          <div style="font-size:11px;color:#9ca3af;text-align:left;margin-bottom:6px;">
            Belum ada foto tersimpan untuk dokumen ini.
          </div>
        `;

      const { value: formValues } = await Swal.fire({
        title: 'Edit Foto & Catatan',
        html: `
          <div style="text-align:left;font-size:12px;margin-bottom:6px;color:#4b5563;">
            ${label}
          </div>

          ${existingHtml}

          <div id="swal-new-preview-wrapper"
               style="text-align:center;margin-bottom:8px;display:none;">
            <div style="font-size:11px;color:#4b5563;margin-bottom:4px;">Preview foto baru:</div>
            <div id="swal-new-preview"
                 style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;"></div>
          </div>

          <div style="text-align:left;margin-bottom:8px;">
            <label style="display:block;font-size:11px;margin-bottom:2px;color:#4b5563;">
              Tambah foto baru (opsional)
            </label>
            <input
              type="file"
              id="swal-new-photos"
              accept="image/*"
              multiple
              class="swal2-input"
              style="height:auto;padding:.35rem .5rem;font-size:12px;"
            >
          </div>

          <div style="text-align:left;">
            <label style="display:block;font-size:11px;margin-bottom:2px;color:#4b5563;">Keterangan</label>
            <textarea
              id="swal-description"
              class="swal2-textarea"
              style="font-size:12px;height:80px;"
            >${description}</textarea>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#318f8c',
        cancelButtonColor: '#e5e7eb',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-full px-4 py-2 text-xs',
          cancelButton: 'rounded-full px-4 py-2 text-xs',
        },
        didOpen: () => {
          const popup = Swal.getPopup();
          const existingContainer = popup.querySelector('#swal-existing-photos');
          const fileInput = popup.querySelector('#swal-new-photos');
          const previewWrapper = popup.querySelector('#swal-new-preview-wrapper');
          const previewContainer = popup.querySelector('#swal-new-preview');

          // Toggle delete untuk foto lama
          if (existingContainer) {
            existingContainer.addEventListener('click', (e) => {
              const wrapper = e.target.closest('[data-photo-id]');
              if (!wrapper) return;
              const idAttr = wrapper.getAttribute('data-photo-id');
              if (!idAttr) return;
              const id = parseInt(idAttr, 10);
              if (Number.isNaN(id)) return;

              if (deleteIds.has(id)) {
                deleteIds.delete(id);
                wrapper.style.opacity = '1';
                wrapper.style.outline = 'none';
                wrapper.style.outlineOffset = '0';
              } else {
                deleteIds.add(id);
                wrapper.style.opacity = '0.5';
                wrapper.style.outline = '2px solid #dc2626';
                wrapper.style.outlineOffset = '2px';
              }
            });
          }

          // Preview foto baru
          if (fileInput && previewWrapper && previewContainer) {
            fileInput.addEventListener('change', (e) => {
              const files = Array.from(e.target.files || []);

              // Bersih preview lama
              previewContainer.innerHTML = '';
              objectUrls.forEach((u) => URL.revokeObjectURL(u));
              objectUrls = [];

              if (!files.length) {
                previewWrapper.style.display = 'none';
                return;
              }

              files.forEach((file) => {
                const url = URL.createObjectURL(file);
                objectUrls.push(url);

                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Preview foto baru';
                img.style.maxWidth = '72px';
                img.style.maxHeight = '72px';
                img.style.borderRadius = '0.75rem';
                img.style.border = '1px solid #e5e7eb';
                img.style.objectFit = 'cover';

                previewContainer.appendChild(img);
              });

              previewWrapper.style.display = 'block';
            });
          }
        },
        willClose: () => {
          objectUrls.forEach((u) => URL.revokeObjectURL(u));
        },
        preConfirm: () => {
          const descEl = document.getElementById('swal-description');
          const fileEl = document.getElementById('swal-new-photos');

          const files = fileEl && fileEl.files ? Array.from(fileEl.files) : [];
          const deletes = Array.from(deleteIds);

          return {
            description: descEl ? descEl.value || '' : '',
            delete_photo_ids: deletes,
            new_photos: files,   // <-- semua file baru
          };
        },
      });

      if (!formValues) return;

      // Kirim via AJAX ke updateUrl
      const formData = new FormData();
      if (csrf) formData.append('_token', csrf);
      formData.append('_method', 'POST');
      formData.append('description', formValues.description || '');

      (formValues.delete_photo_ids || []).forEach((id) => {
        formData.append('delete_photo_ids[]', id);
      });

      (formValues.new_photos || []).forEach((file) => {
        formData.append('new_photos[]', file);
      });

      try {
        const response = await fetch(updateUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        if (!response.ok) {
          const text = await response.text();
          console.error('Update error:', text);
          throw new Error('Gagal menyimpan perubahan');
        }

        await Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Foto dan keterangan berhasil diperbarui.',
          confirmButtonColor: '#318f8c',
          customClass: { popup: 'rounded-2xl' },
        });

        window.location.reload();
      } catch (e) {
        await Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: e.message || 'Terjadi kesalahan saat menyimpan.',
          confirmButtonColor: '#ef4444',
          customClass: { popup: 'rounded-2xl' },
        });
      }
    });
  });
}


function initPhotoViewer() {
  const buttons = document.querySelectorAll('.btn-view-photos');
  if (!buttons.length) return;

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const label = btn.getAttribute('data-label') || 'Foto dokumen';
      let photos = [];

      try {
        const raw = btn.getAttribute('data-photos') || '[]';
        photos = JSON.parse(raw) || [];
      } catch (e) {
        photos = [];
      }

      if (!photos.length) return;

      let currentIndex = 0;

      const buildHtml = () => `
        <div style="display:flex;flex-direction:column;gap:8px;max-width:100%;">
          <div style="font-size:12px;color:#4b5563;text-align:left;">
            ${label}
          </div>

          <div style="position:relative;display:flex;justify-content:center;align-items:center;">
            <button
              type="button"
              id="swal-photo-prev"
              style="position:absolute;left:4px;top:50%;transform:translateY(-50%);
                     border-radius:999px;border:none;padding:6px 8px;font-size:12px;
                     background:#00000080;color:#ffffff;cursor:pointer;">
              ‹
            </button>

            <img
              id="swal-photo-main"
              src="${photos[0]}"
              alt="Foto dokumen"
              style="max-width:100%;max-height:60vh;border-radius:0.75rem;border:1px solid #e5e7eb;object-fit:contain;"
            />

            <button
              type="button"
              id="swal-photo-next"
              style="position:absolute;right:4px;top:50%;transform:translateY(-50%);
                     border-radius:999px;border:none;padding:6px 8px;font-size:12px;
                     background:#00000080;color:#ffffff;cursor:pointer;">
              ›
            </button>
          </div>

          <div style="font-size:11px;color:#6b7280;text-align:center;">
            <span id="swal-photo-counter">1 / ${photos.length}</span>
          </div>

          ${
            photos.length > 1
              ? `
              <div
                id="swal-photo-thumbs"
                style="display:flex;flex-wrap:nowrap;overflow-x:auto;gap:6px;padding-bottom:4px;justify-content:center;"
              >
                ${photos
                  .map(
                    (url, idx) => `
                      <img
                        src="${url}"
                        data-index="${idx}"
                        alt="Thumb"
                        style="width:56px;height:56px;border-radius:0.65rem;
                               border:${idx === 0 ? '2px solid #10b981' : '1px solid #e5e7eb'};
                               object-fit:cover;cursor:pointer;flex-shrink:0;"
                      />
                    `
                  )
                  .join('')}
              </div>
            `
              : ''
          }
        </div>
      `;

      Swal.fire({
        title: 'Detail Foto',
        html: buildHtml(),
        width: '600px',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
          popup: 'rounded-2xl',
        },
        didOpen: () => {
          const popup = Swal.getPopup();
          const imgMain = popup.querySelector('#swal-photo-main');
          const btnPrev = popup.querySelector('#swal-photo-prev');
          const btnNext = popup.querySelector('#swal-photo-next');
          const counter = popup.querySelector('#swal-photo-counter');
          const thumbsContainer = popup.querySelector('#swal-photo-thumbs');

          const updateView = () => {
            if (!imgMain || !counter) return;
            imgMain.src = photos[currentIndex];
            counter.textContent = `${currentIndex + 1} / ${photos.length}`;

            if (thumbsContainer) {
              const thumbs = thumbsContainer.querySelectorAll('img[data-index]');
              thumbs.forEach((thumb) => {
                const idx = parseInt(thumb.getAttribute('data-index'), 10);
                if (idx === currentIndex) {
                  thumb.style.border = '2px solid #10b981';
                } else {
                  thumb.style.border = '1px solid #e5e7eb';
                }
              });
            }
          };

          if (btnPrev) {
            btnPrev.addEventListener('click', () => {
              currentIndex = (currentIndex - 1 + photos.length) % photos.length;
              updateView();
            });
          }

          if (btnNext) {
            btnNext.addEventListener('click', () => {
              currentIndex = (currentIndex + 1) % photos.length;
              updateView();
            });
          }

          if (thumbsContainer) {
            thumbsContainer.addEventListener('click', (e) => {
              const target = e.target;
              if (!(target instanceof HTMLElement)) return;
              const idxAttr = target.getAttribute('data-index');
              if (idxAttr == null) return;
              const idx = parseInt(idxAttr, 10);
              if (Number.isNaN(idx)) return;

              currentIndex = idx;
              updateView();
            });
          }

          // allow click on main image to open full tab (optional)
          if (imgMain) {
            imgMain.style.cursor = 'pointer';
            imgMain.addEventListener('click', () => {
              window.open(photos[currentIndex], '_blank');
            });
          }

          updateView();
        },
      });
    });
  });
}

function initCashierDelete() {
  const buttons = document.querySelectorAll('.btn-delete-doc');
  if (!buttons.length) return;

  const csrf = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const deleteUrl = btn.getAttribute('data-delete-url');
      const label = btn.getAttribute('data-label') || 'dokumen kasir';

      if (!deleteUrl) return;

      const result = await Swal.fire({
        title: 'Hapus dokumen?',
        html: `
          <div style="font-size:13px;color:#4b5563;text-align:left;">
            Anda akan menghapus <span style="font-weight:600;">${label}</span>.<br>
            Semua foto yang terkait dengan dokumen ini juga akan dihapus.<br><br>
            Tindakan ini <span style="color:#b91c1c;font-weight:600;">tidak dapat dibatalkan</span>.
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-full px-4 py-2 text-xs',
          cancelButton: 'rounded-full px-4 py-2 text-xs',
        },
      });

      if (!result.isConfirmed) return;

      const formData = new FormData();
      if (csrf) formData.append('_token', csrf);
      formData.append('_method', 'DELETE');

      try {
        const response = await fetch(deleteUrl, {
          method: 'POST', // karena kita pakai _method=DELETE
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        if (!response.ok) {
          const text = await response.text();
          console.error('Delete error:', text);
          throw new Error('Gagal menghapus dokumen.');
        }

        await Swal.fire({
          icon: 'success',
          title: 'Terhapus',
          text: 'Dokumen kasir berhasil dihapus.',
          confirmButtonColor: '#318f8c',
          customClass: { popup: 'rounded-2xl' },
        });

        window.location.reload();
      } catch (e) {
        await Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: e.message || 'Terjadi kesalahan saat menghapus dokumen.',
          confirmButtonColor: '#ef4444',
          customClass: { popup: 'rounded-2xl' },
        });
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initCashierConfirm();
  initCashierEdit();
  initPhotoViewer();
  initCashierDelete();
});