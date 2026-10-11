document.addEventListener('DOMContentLoaded', () => {
    handleFlashMessage()
    handleDeleteConfirmation()
})

function handleFlashMessage() {
    const flash = window.supplierFlash || null

    if (!flash || !window.Swal) return

    if (flash.success) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: flash.success,
            timer: 1800,
            showConfirmButton: false,
        })
    }

    if (flash.error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: flash.error,
        })
    }
}

function handleDeleteConfirmation() {
    const deleteForms = document.querySelectorAll('[data-supplier-delete-form]')

    deleteForms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault()

            if (!window.Swal) {
                form.submit()
                return
            }

            Swal.fire({
                title: 'Hapus supplier?',
                text: 'Data supplier yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit()
                }
            })
        })
    })
}