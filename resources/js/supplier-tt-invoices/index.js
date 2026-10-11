document.addEventListener('DOMContentLoaded', () => {
    handleSupplierTtDeleteConfirmation()
    handleSupplierTtActionConfirmation()
})

function handleSupplierTtDeleteConfirmation() {
    const deleteForms = document.querySelectorAll('[data-supplier-tt-delete-form]')

    deleteForms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault()

            if (!window.Swal) {
                form.submit()
                return
            }

            Swal.fire({
                title: 'Hapus Faktur TT?',
                text: 'Data faktur, foto, dan potongan terkait akan ikut terhapus.',
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

function handleSupplierTtActionConfirmation() {
    document.querySelectorAll('[data-supplier-tt-action-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault()

            const submit = () => {
                if (form.hasAttribute('data-download-reload')) {
                    downloadThenReload(form)
                    return
                }

                form.submit()
            }

            if (!window.Swal) {
                if (window.confirm(form.dataset.confirmText || 'Lanjutkan proses ini?')) submit()
                return
            }

            Swal.fire({
                title: form.dataset.confirmTitle || 'Konfirmasi',
                text: form.dataset.confirmText || 'Pastikan data sudah benar.',
                icon: form.dataset.confirmIcon || 'question',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: form.dataset.confirmColor || '#318f8c',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) submit()
            })
        })
    })
}

async function downloadThenReload(form) {
    if (window.Swal) {
        Swal.fire({
            title: 'Mengunci batch',
            text: 'PDF sedang disiapkan...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        })
    }

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })

        if (!response.ok || !String(response.headers.get('content-type')).includes('application/pdf')) {
            window.location.reload()
            return
        }

        const disposition = response.headers.get('content-disposition') || ''
        const encodedName = disposition.match(/filename\*=UTF-8''([^;]+)/i)?.[1]
        const regularName = disposition.match(/filename="?([^";]+)"?/i)?.[1]
        const filename = encodedName ? decodeURIComponent(encodedName) : (regularName || 'batch-pembayaran-tt.pdf')
        const url = URL.createObjectURL(await response.blob())
        const link = document.createElement('a')
        link.href = url
        link.download = filename
        document.body.appendChild(link)
        link.click()
        link.remove()
        setTimeout(() => URL.revokeObjectURL(url), 1000)
        setTimeout(() => window.location.reload(), 350)
    } catch (error) {
        if (window.Swal) Swal.close()
        alert('Gagal mengunci batch atau mengunduh PDF. Silakan coba kembali.')
    }
}
