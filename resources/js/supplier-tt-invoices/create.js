document.addEventListener('alpine:init', () => {
    Alpine.data('supplierTtInvoiceWizard', (config = {}) => {
        const initial = config.initial || {}

        return ({
        currentStep: 1,
        totalSteps: 3,
        isSubmitting: false,
        isCompressing: false,
        loadingGoodsReceipts: false,

        suppliers: config.suppliers || [],
        availableGoodsReceiptUrl: config.availableGoodsReceiptUrl,

        supplierDropdownOpen: false,
        supplierSearch: '',
        selectedSupplierId: String(initial.supplierId || ''),
        branchId: String(initial.branchId || ''),
        selectedSupplierLabel: initial.supplierLabel || '',

        goodsReceipts: [],
        selectedGoodsReceiptId: String(initial.goodsReceiptId || ''),

        invoiceNumber: initial.invoiceNumber || '',
        invoiceDate: initial.invoiceDate || '',
        receivedDate: initial.receivedDate || new Date().toISOString().slice(0, 10),
        dueDate: initial.dueDate || '',
        grossNominal: String(initial.grossNominal || ''),
        notes: initial.notes || '',

        photos: initial.photos || [],
        deletedPhotoIds: [],
        deductions: initial.deductions || [],

        steps: [
            {
                number: 1,
                title: 'Data Supplier',
                description: 'Pilih supplier dan goods receipt',
            },
            {
                number: 2,
                title: 'Detail Faktur',
                description: 'Input identitas faktur dan foto',
            },
            {
                number: 3,
                title: 'Potongan & Review',
                description: 'Cek nominal akhir',
            },
        ],

        get currentStepInfo() {
            return this.steps[this.currentStep - 1] || this.steps[0]
        },

        get nextStepInfo() {
            return this.currentStep < this.totalSteps ? this.steps[this.currentStep] : null
        },

        get stepProgress() {
            return `${(this.currentStep / this.totalSteps) * 100}%`
        },

        get stepLineProgress() {
            if (this.totalSteps <= 1) return '100%'
            return `${((this.currentStep - 1) / (this.totalSteps - 1)) * 100}%`
        },

        init() {
            if (this.selectedSupplierId) {
                this.fetchAvailableGoodsReceipts()
            }

            this.$nextTick(() => {
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('goods-receipt:page-ready'))
                }, 100)
            })
        },

        get filteredSuppliers() {
            const keyword = this.supplierSearch.toLowerCase().trim()

            if (!keyword) {
                return this.suppliers
            }

            return this.suppliers.filter((supplier) =>
                String(supplier.label).toLowerCase().includes(keyword)
            )
        },

        get selectedGoodsReceipt() {
            return this.goodsReceipts.find((receipt) =>
                String(receipt.id) === String(this.selectedGoodsReceiptId)
            ) || null
        },

        get grossNominalNumber() {
            return this.parseNumber(this.grossNominal)
        },

        get deductionTotal() {
            return this.deductions.reduce((total, deduction) => {
                return total + this.parseNumber(deduction.amount)
            }, 0)
        },

        get netNominal() {
            return Math.max(0, this.grossNominalNumber - this.deductionTotal)
        },

        get isNetNominalValid() {
            if (!this.selectedGoodsReceipt) {
                return true
            }

            return this.netNominal <= Number(this.selectedGoodsReceipt.remaining_nominal || 0)
        },

        goToStep(step) {
            if (step < 1 || step > this.totalSteps) return
            this.currentStep = step
            this.scrollToTop()
        },

        nextStep() {
            const validation = this.validateStep(this.currentStep)

            if (!validation.valid) {
                this.showValidationErrors(validation.errors)
                return
            }

            if (this.currentStep < this.totalSteps) {
                this.currentStep++
                this.scrollToTop()
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--
                this.scrollToTop()
            }
        },

        chooseSupplier(supplier) {
            this.selectedSupplierId = String(supplier.id)
            this.selectedSupplierLabel = supplier.label
            this.supplierDropdownOpen = false
            this.supplierSearch = ''
            this.selectedGoodsReceiptId = ''
            this.goodsReceipts = []

            this.fetchAvailableGoodsReceipts()
        },

        async fetchAvailableGoodsReceipts() {
            if (!this.selectedSupplierId || !this.branchId || !this.availableGoodsReceiptUrl) return

            this.loadingGoodsReceipts = true

            try {
                const url = new URL(this.availableGoodsReceiptUrl, window.location.origin)
                url.searchParams.set('supplier_id', this.selectedSupplierId)
                url.searchParams.set('branch_id', this.branchId)
                if (config.invoiceId) {
                    url.searchParams.set('exclude_tt_invoice_id', config.invoiceId)
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                    },
                })

                const result = await response.json()

                if (!response.ok) {
                    throw new Error(result.message || 'Gagal mengambil goods receipt.')
                }

                this.goodsReceipts = result.data || []
            } catch (error) {
                this.goodsReceipts = []
                this.showWarning(error.message || 'Gagal mengambil goods receipt available.')
            } finally {
                this.loadingGoodsReceipts = false
            }
        },

        chooseGoodsReceipt() {
            // selectedGoodsReceipt is computed, no extra action needed for now.
        },

        formatGoodsReceiptOption(receipt) {
            return [
                `GR #${receipt.id}`,
                receipt.received_date || '-',
                `Total Rp ${this.formatCurrency(receipt.total_nominal)}`,
                `Sisa Rp ${this.formatCurrency(receipt.remaining_nominal)}`,
                receipt.tt_count_label,
            ].join(' | ')
        },

        addDeduction() {
            this.deductions.push({
                id: Date.now() + Math.random(),
                type: 'other',
                amount: '',
                reference_number: '',
                reference_date: '',
                description: '',
            })
        },

        removeDeduction(index) {
            this.deductions.splice(index, 1)
        },

        normalizeGrossNominal() {
            this.grossNominal = this.onlyDigits(this.grossNominal)
        },

        normalizeDeduction(index) {
            this.deductions[index].amount = this.onlyDigits(this.deductions[index].amount)
        },

        async handlePhotos(event) {
            const input = event.target
            const files = Array.from(input.files || [])

            if (!files.length) return

            this.isCompressing = true

            try {
                for (const file of files) {
                    if (!file.type.startsWith('image/')) {
                        this.showWarning(`File ${file.name} bukan gambar.`)
                        continue
                    }

                    const compressedFile = await this.compressImage(file, {
                        maxWidth: 1280,
                        maxHeight: 1280,
                        quality: 0.72,
                        outputType: 'image/jpeg',
                    })

                    this.photos.push({
                        id: Date.now() + Math.random(),
                        file: compressedFile,
                        preview: URL.createObjectURL(compressedFile),
                        name: compressedFile.name,
                        originalSize: file.size,
                        size: compressedFile.size,
                    })
                }

                const canonicalInput = document.querySelector('[data-supplier-tt-photo-input]')

                if (canonicalInput) {
                    this.syncPhotoInput(canonicalInput)
                }

                if (input !== canonicalInput) {
                    input.value = ''
                }
            } catch (error) {
                this.showWarning(error.message || 'Gagal compress foto.')
            } finally {
                this.isCompressing = false
            }
        },

        syncPhotoInput(input) {
            const dataTransfer = new DataTransfer()

            this.photos.forEach((photo) => {
                if (photo.file) {
                    dataTransfer.items.add(photo.file)
                }
            })

            input.files = dataTransfer.files
        },

        removePhoto(index) {
            const photo = this.photos[index]

            if (photo?.existingId) {
                this.deletedPhotoIds.push(photo.existingId)
            }

            if (photo?.preview) {
                URL.revokeObjectURL(photo.preview)
            }

            this.photos.splice(index, 1)

            const input = document.querySelector('[data-supplier-tt-photo-input]')

            if (input) {
                this.syncPhotoInput(input)
            }
        },

        async compressImage(file, options = {}) {
            const {
                maxWidth = 1280,
                maxHeight = 1280,
                quality = 0.72,
                outputType = 'image/jpeg',
            } = options

            const image = await this.loadImage(file)

            let { width, height } = image

            if (width > maxWidth || height > maxHeight) {
                const ratio = Math.min(maxWidth / width, maxHeight / height)
                width = Math.round(width * ratio)
                height = Math.round(height * ratio)
            }

            const canvas = document.createElement('canvas')
            canvas.width = width
            canvas.height = height

            const context = canvas.getContext('2d')
            context.drawImage(image, 0, 0, width, height)

            const blob = await new Promise((resolve) => {
                canvas.toBlob(resolve, outputType, quality)
            })

            if (!blob) {
                throw new Error('Gagal compress gambar.')
            }

            const originalName = file.name.replace(/\.[^/.]+$/, '')
            const compressedName = `${originalName}.jpg`

            return new File([blob], compressedName, {
                type: outputType,
                lastModified: Date.now(),
            })
        },

        loadImage(file) {
            return new Promise((resolve, reject) => {
                const image = new Image()
                const url = URL.createObjectURL(file)

                image.onload = () => {
                    URL.revokeObjectURL(url)
                    resolve(image)
                }

                image.onerror = () => {
                    URL.revokeObjectURL(url)
                    reject(new Error('Gagal membaca gambar.'))
                }

                image.src = url
            })
        },

        validateStep(step, action = 'verify') {
            const errors = []

            if (step === 1) {
                if (!this.branchId) {
                    errors.push('Cabang asal wajib dipilih.')
                }

                if (!this.selectedSupplierId) {
                    errors.push('Supplier wajib dipilih.')
                }

            }

            if (step === 2) {
                if (!this.invoiceNumber.trim()) {
                    errors.push('Nomor Faktur TT wajib diisi.')
                }

                if (!this.invoiceDate) {
                    errors.push('Tanggal faktur wajib diisi.')
                }

                if (!this.receivedDate) {
                    errors.push('Tanggal terima faktur wajib diisi.')
                }

                if (!this.dueDate) {
                    errors.push('Due date wajib diisi.')
                }

                if (this.grossNominalNumber <= 0) {
                    errors.push('Nominal bruto wajib lebih dari 0.')
                }

                if (this.photos.length < 1) {
                    errors.push('Minimal upload 1 foto Faktur TT.')
                }
            }

            if (step === 3) {
                if (this.deductionTotal > this.grossNominalNumber) {
                    errors.push('Total potongan tidak boleh melebihi nominal bruto.')
                }

                if (this.selectedGoodsReceipt && !this.isNetNominalValid) {
                    errors.push('Net nominal Faktur TT melebihi sisa available Goods Receipt.')
                }
            }

            return {
                valid: errors.length === 0,
                errors,
            }
        },

        submitAs(action) {
            if (this.isCompressing) {
                this.showWarning('Tunggu sampai proses compress foto selesai.')
                return
            }

            if (this.isSubmitting) {
                return
            }

            for (let step = 1; action !== 'draft' && step <= this.totalSteps; step++) {
                const validation = this.validateStep(step, action)

                if (!validation.valid) {
                    this.currentStep = step
                    this.scrollToTop()
                    this.showValidationErrors(validation.errors)
                    return
                }
            }

            const submitAction = document.querySelector('[name="submit_action"]')

            if (submitAction) {
                submitAction.value = action
            }

            const form = document.querySelector('[data-supplier-tt-form]')

            if (!form) {
                this.showWarning('Form Faktur TT tidak ditemukan.')
                return
            }

            this.isSubmitting = true
            form.submit()
        },

        parseNumber(value) {
            const normalized = String(value ?? '').replace(/[^\d]/g, '')

            if (!normalized) return 0

            const number = Number(normalized)

            return Number.isFinite(number) ? number : 0
        },

        onlyDigits(value) {
            return String(value ?? '').replace(/[^\d]/g, '')
        },

        formatCurrency(value) {
            const number = Number(value || 0)

            return new Intl.NumberFormat('id-ID').format(number)
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 KB'

            const kb = bytes / 1024

            if (kb < 1024) {
                return `${kb.toFixed(1)} KB`
            }

            return `${(kb / 1024).toFixed(2)} MB`
        },

        showWarning(message) {
            if (!window.Swal) {
                alert(message)
                return
            }

            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: message,
                confirmButtonText: 'Mengerti',
            })
        },

        showValidationErrors(errors = []) {
            if (!errors.length) return

            if (!window.Swal) {
                alert(errors.join('\n'))
                return
            }

            Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap',
                html: `
                    <div style="text-align:left">
                        <p style="margin-bottom:8px">Lengkapi data berikut:</p>
                        <ul style="padding-left:18px; list-style:disc;">
                            ${errors.map((error) => `<li>${this.escapeHtml(error)}</li>`).join('')}
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Mengerti',
            })
        },

        escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;')
        },

        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            })
        },
        })
    })
})
