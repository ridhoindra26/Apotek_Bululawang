document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('[data-delete-short-url]');

    deleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            if (window.Swal) {
                Swal.fire({
                    title: 'Hapus short link?',
                    text: 'Data short link dan file QR akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

                return;
            }

            if (confirm('Hapus short link ini?')) {
                form.submit();
            }
        });
    });

    const copyButtons = document.querySelectorAll('[data-copy-text]');

    copyButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const text = button.dataset.copyText;

            try {
                await navigator.clipboard.writeText(text);

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Link berhasil disalin.',
                        timer: 1400,
                        showConfirmButton: false,
                    });
                } else {
                    alert('Link berhasil disalin.');
                }
            } catch (error) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak bisa menyalin link.',
                    });
                } else {
                    alert('Tidak bisa menyalin link.');
                }
            }
        });
    });
});