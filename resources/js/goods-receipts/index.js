document.addEventListener('DOMContentLoaded', () => {
    handleGoodsReceiptFlashMessage()
    handleGoodsReceiptDeleteConfirmation()
    handleGoodsReceiptApproveConfirmation()
    handleGoodsReceiptRejectConfirmation()
    handleGoodsReceiptCancelConfirmation()
})

function handleGoodsReceiptFlashMessage() {

    console.log('goodsReceiptFlash:', window.goodsReceiptFlash)
    console.log('appFlash:', window.appFlash)

    const flash = window.goodsReceiptFlash || null

    if (!flash || !window.Swal) return

    if (flash.success) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: flash.success,
            timer: 1800,
            showConfirmButton: false,
        })

        return
    }

    if (flash.error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: flash.error,
        })

        return
    }

    if (Array.isArray(flash.validationErrors) && flash.validationErrors.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi gagal',
            html: `
                <div class="text-left">
                    <p class="mb-2">Periksa kembali data berikut:</p>
                    <ul style="padding-left: 1.2rem; list-style: disc;">
                        ${flash.validationErrors.map((error) => `<li>${escapeHtml(error)}</li>`).join('')}
                    </ul>
                </div>
            `,
            confirmButtonText: 'Okayy',
        })
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}

function handleGoodsReceiptDeleteConfirmation() {
    const deleteForms = document.querySelectorAll('[data-goods-receipt-delete-form]')

    deleteForms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault()

            if (!window.Swal) {
                form.submit()
                return
            }

            Swal.fire({
                title: 'Hapus penerimaan barang?',
                text: 'Data penerimaan, faktur, foto, dan tanda tangan terkait akan ikut terhapus.',
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

function handleGoodsReceiptRejectConfirmation() {
    const rejectForms = document.querySelectorAll('[data-reject-form]')

    rejectForms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault()

            if (!window.Swal) {
                form.submit()
                return
            }

            Swal.fire({
                title: 'Tolak penerimaan barang?',
                input: 'textarea',
                inputLabel: 'Catatan penolakan',
                inputPlaceholder: 'Tulis alasan penolakan...',
                inputValidator: (value) => {
                    if (!String(value || '').trim()) {
                        return 'Alasan penolakan wajib diisi.'
                    }
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tolak',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) return

                const noteInput = form.querySelector('[name="evaluation_note"]')

                if (noteInput) {
                    noteInput.value = String(result.value || '').trim()
                }

                form.submit()
            })
        })
    })
}

function handleGoodsReceiptCancelConfirmation() {
    const cancelForms = document.querySelectorAll('[data-cancel-form]')

    cancelForms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault()

            if (!window.Swal) {
                form.submit()
                return
            }

            Swal.fire({
                title: 'Batalkan penerimaan barang?',
                text: 'Status data akan berubah menjadi cancelled.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Tidak',
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

function handleGoodsReceiptApproveConfirmation() {
    const approveForms = document.querySelectorAll('[data-approve-form]')

    approveForms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault()

            if (!window.Swal) {
                form.submit()
                return
            }

            Swal.fire({
                title: 'Approve penerimaan barang?',
                text: 'Pastikan data penerimaan dan seluruh evaluasi faktur sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, approve',
                cancelButtonText: 'Periksa lagi',
                confirmButtonColor: '#16a34a',
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
