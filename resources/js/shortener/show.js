document.addEventListener('DOMContentLoaded', () => {
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

    const modal = document.getElementById('qr-preview-modal');
    const modalImage = document.getElementById('qr-preview-image');
    const modalTitle = document.getElementById('qr-preview-title');
    const modalDownload = document.getElementById('qr-preview-download');
    const modalClose = document.getElementById('qr-preview-close');
    const openButtons = document.querySelectorAll('[data-open-qr-preview]');

    const closeModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modalImage.src = '';
        modalTitle.textContent = '';
        modalDownload.href = '#';
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const url = button.dataset.qrUrl;
            const title = button.dataset.qrTitle ?? '';

            if (!modal || !url) return;

            modalImage.src = url;
            modalTitle.textContent = title;
            modalDownload.href = url;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});