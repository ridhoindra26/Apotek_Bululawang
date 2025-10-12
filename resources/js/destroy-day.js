import Swal from 'sweetalert2';

// Jalankan setelah DOM siap
document.addEventListener('DOMContentLoaded', () => {
  const btnDestroy = document.getElementById('btnDestroyJadwal');

  if (btnDestroy) {
    btnDestroy.addEventListener('click', () => {
      const url = btnDestroy.dataset.url;
      confirmDestroy(url);
    });
  }
});

function confirmDestroy(url) {
  Swal.fire({
    title: 'Yakin ingin menyusun ulang?',
    text: 'Semua perubahan pada jadwal ini akan dihapus permanen.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      // Buat form DELETE dinamis
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = url;

      const token = document.createElement('input');
      token.type = 'hidden';
      token.name = '_token';
      token.value = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';

      const method = document.createElement('input');
      method.type = 'hidden';
      method.name = '_method';
      method.value = 'DELETE';

      form.appendChild(token);
      form.appendChild(method);
      document.body.appendChild(form);
      form.submit();
    }
  });
}
