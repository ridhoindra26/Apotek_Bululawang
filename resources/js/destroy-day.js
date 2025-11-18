import Swal from 'sweetalert2';

// Jalankan setelah DOM siap
document.addEventListener('DOMContentLoaded', () => {
  const btnDestroy = document.getElementById('btnDestroyJadwal');

  if (btnDestroy) {
    btnDestroy.addEventListener('click', () => {
      const url = btnDestroy.dataset.url;
      const bulan = btnDestroy.dataset.bulan;
      const tahun = btnDestroy.dataset.tahun;
      confirmDestroy(url, bulan, tahun);
    });
  }
});

function confirmDestroy(url, bulan, tahun) {
  const now = new Date();

  const currentMonth = now.getMonth() + 1;      // 1–12
  const currentYear  = now.getFullYear();

  const scheduleMonth = parseInt(bulan, 10);    // normalisasi "01" -> 1
  const scheduleYear  = parseInt(tahun, 10);

  // Representasikan sebagai angka "urut" biar gampang bandingkan
  const scheduleIndex = scheduleYear * 12 + scheduleMonth;
  const currentIndex  = currentYear * 12 + currentMonth;

  if (scheduleIndex <= currentIndex) {
    Swal.fire({
      icon: 'error',
      title: 'Tidak Bisa Menyusun Ulang Jadwal!',
      text: 'Jadwal hanya bisa disusun ulang untuk bulan mendatang (mulai bulan depan).',
      confirmButtonText: 'OK',
      confirmButtonColor: '#318f8c'
    });
    return;
  }

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
