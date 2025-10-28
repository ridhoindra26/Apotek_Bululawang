import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const modal    = document.getElementById('adjustModal');
  const openBtn  = document.querySelector('.js-open-adjust');
  const closeBtn = document.getElementById('adjustCloseBtn');
  const cancelBtn= document.getElementById('adjustCancelBtn');
  const form     = document.getElementById('adjustForm');

  // Open / close helpers
  const openModal = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
  const closeModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };

  // Open adjust modal
  openBtn?.addEventListener('click', openModal);

  // Close actions
  closeBtn?.addEventListener('click', closeModal);
  cancelBtn?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Confirm before submit
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const note = fd.get('note');
    const penalty = fd.get('penalty_minutes');
    const overtime = fd.get('overtime_applied_minutes');

    const { isConfirmed } = await Swal.fire({
      title: 'Confirm adjustment?',
      html: `<div class="text-left">
                <div class="mb-2 text-emerald-700"><b>Overtime add:</b> ${overtime || '-'}</div>
                <div class="mb-2 text-rose-700"><b>Penalty add:</b> ${penalty || '-'}</div>
                <div><b>Note:</b> ${note || '-'}</div>
             </div>`,
      icon: 'question',
      iconColor: "#318f8c",
      showCancelButton: true,
      confirmButtonColor: "#318f8c",
      confirmButtonText: 'Yes, save',
      cancelButtonText: 'Cancel',
    });

    if (isConfirmed) form.submit();
  });

  // Show flash alerts if any
  const flash = document.getElementById('flash-data');
  if (flash) {
    const success = flash.getAttribute('data-success');
    const error   = flash.getAttribute('data-error');

    if (success) {
      Swal.fire({ title: 'Success', text: success, icon: 'success' });
    } else if (error) {
      Swal.fire({ title: 'Failed', text: error, icon: 'error' });
    }
  }

  // “Show more” note toggler
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-show-more');
    if (!btn) return;
    const cell = btn.closest('td');
    const noteEl = cell?.querySelector('.js-note');
    if (!noteEl) return;

    const expanded = noteEl.classList.toggle('truncate'); // toggle back if needed
    if (expanded) {
      // if class is present, it's truncated
      noteEl.classList.add('truncate');
      btn.textContent = 'Show more';
    } else {
      // remove truncate to expand
      noteEl.classList.remove('truncate');
      noteEl.style.whiteSpace = 'normal';
      noteEl.style.wordBreak = 'break-word';
      btn.textContent = 'Show less';
    }
  });
});
