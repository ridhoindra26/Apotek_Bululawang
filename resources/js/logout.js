import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
  const logoutBtn = document.getElementById('logout-button');
  const logoutForm = document.getElementById('logout-form');

  if (!logoutBtn || !logoutForm) return;

  logoutBtn.addEventListener('click', async () => {
    const result = await Swal.fire({
      title: 'Yakin keluar kah??',
      text: 'Kamu akan keluar dari website ini.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#318f8c',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, aku pingin keluar',
      cancelButtonText: 'Tidak jadi hehe',
      reverseButtons: true,
    });

    if (result.isConfirmed) logoutForm.submit();
  });
});
