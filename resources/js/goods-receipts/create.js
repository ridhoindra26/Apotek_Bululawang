document.addEventListener('alpine:init', () => {
    Alpine.data('goodsReceiptWizard', (config = {}) => ({
        currentStep: 1,
        totalSteps: 3,
        isCompressing: false,
        isSubmitting: false,

        deletedInvoiceIds: [],
        deletedPhotoIds: [],

        steps: [
            {
                number: 1,
                title: 'Informasi Penerimaan',
                description: 'Data utama penerimaan barang',
            },
            {
                number: 2,
                title: 'Dokumentasi Faktur',
                description: 'Faktur, nominal, dan foto halaman',
            },
            {
                number: 3,
                title: 'Evaluasi & Persetujuan',
                description: 'Evaluasi, tanda tangan, dan submit',
            },
        ],

        get currentStepInfo() {
            return this.steps.find((step) => step.number === this.currentStep) || this.steps[0]
        },

        get nextStepInfo() {
            return this.steps.find((step) => step.number === this.currentStep + 1) || null
        },

        get stepProgress() {
            return `${(this.currentStep / this.totalSteps) * 100}%`
        },

        invoices: config.invoices?.length
            ? config.invoices
            : [
                {
                    id: Date.now(),
                    existing_id: null,
                    invoice_number: '',
                    invoice_date: '',
                    nominal: '',
                    evaluation_status: 'unchecked',
                    evaluation_note: '',
                    pages: [],
                },
            ],
        
        init() {
            const errors = window.goodsReceiptFlash?.validationErrors || []

            if (errors.length > 0) {
                const joinedErrors = errors.join(' ').toLowerCase()

                if (
                    joinedErrors.includes('faktur') ||
                    joinedErrors.includes('nominal') ||
                    joinedErrors.includes('foto')
                ) {
                    this.currentStep = 2
                }

                if (
                    joinedErrors.includes('evaluasi') ||
                    joinedErrors.includes('signature') ||
                    joinedErrors.includes('tanda tangan')
                ) {
                    this.currentStep = 3
                    this.refreshSignaturePadsIfNeeded()
                }
            }

            this.$nextTick(() => {
                setTimeout(() => {
                    window.dispatchEvent(
                        new CustomEvent('goods-receipt:page-ready')
                    )
                }, 100)
            })
        },

        goToStep(step) {
            if (step < 1 || step > this.totalSteps) return
            this.currentStep = step
            this.scrollToTop()
            this.refreshSignaturePadsIfNeeded()
        },

        nextStep() {
            if (this.currentStep >= this.totalSteps) return
            this.currentStep++
            this.scrollToTop()
            this.refreshSignaturePadsIfNeeded()
        },

        previousStep() {
            if (this.currentStep <= 1) return
            this.currentStep--
            this.scrollToTop()
            this.refreshSignaturePadsIfNeeded()
        },

        isStepActive(step) {
            return this.currentStep === step
        },

        isStepCompleted(step) {
            return this.currentStep > step
        },

        addInvoice() {
            this.invoices.push({
                id: Date.now() + Math.random(),
                existing_id: null,
                invoice_number: '',
                invoice_date: '',
                nominal: '',
                evaluation_status: 'unchecked',
                evaluation_note: '',
                pages: [],
            })

            this.$nextTick(() => {
                const cards = document.querySelectorAll('[data-invoice-card]')
                const lastCard = cards[cards.length - 1]

                if (lastCard) {
                    lastCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    })
                }
            })
        },

        removeInvoice(index) {
            if (this.invoices.length <= 1) {
                this.showWarning('Minimal harus ada 1 faktur.')
                return
            }

            const invoice = this.invoices[index]

            if (invoice?.existing_id) {
                this.deletedInvoiceIds.push(invoice.existing_id)
            }

            invoice.pages.forEach((page) => {
                if (page?.existing_id) {
                    this.deletedPhotoIds.push(page.existing_id)
                }
            })

            this.revokeInvoicePages(index)
            this.invoices.splice(index, 1)
        },

        async compressImage(file, options = {}) {
            const {
                maxWidth = 1280,
                maxHeight = 1280,
                quality = 0.72,
                outputType = 'image/jpeg',
            } = options

            if (!file.type.startsWith('image/')) {
                throw new Error('File bukan gambar.')
            }

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

        formatFileSize(bytes) {
            if (!bytes) return '0 KB'

            const kb = bytes / 1024

            if (kb < 1024) {
                return `${kb.toFixed(1)} KB`
            }

            return `${(kb / 1024).toFixed(2)} MB`
        },

        async handleInvoicePages(event, invoiceIndex) {
            const input = event.target
            const files = Array.from(input.files || [])

            if (!files.length) return

            this.isCompressing = true

            try {
                const compressedFiles = []

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

                    compressedFiles.push(compressedFile)

                    const preview = URL.createObjectURL(compressedFile)

                    this.invoices[invoiceIndex].pages.push({
                        id: Date.now() + Math.random(),
                        file: compressedFile,
                        preview,
                        name: compressedFile.name,
                        originalSize: file.size,
                        size: compressedFile.size,
                    })
                }

                this.syncInvoiceFileInput(input, invoiceIndex)
            } catch (error) {
                this.showWarning(error.message || 'Gagal compress gambar.')
            } finally {
                this.isCompressing = false
            }
        },

        syncInvoiceFileInput(input, invoiceIndex) {
            const dataTransfer = new DataTransfer()

            this.invoices[invoiceIndex].pages.forEach((page) => {
                if (page.file && !page.is_existing) {
                    dataTransfer.items.add(page.file)
                }
            })

            input.files = dataTransfer.files
        },

        removeInvoicePage(invoiceIndex, pageIndex) {
            const page = this.invoices[invoiceIndex].pages[pageIndex]

            if (page?.existing_id) {
                this.deletedPhotoIds.push(page.existing_id)
            }

            if (page?.preview && !page?.is_existing) {
                URL.revokeObjectURL(page.preview)
            }

            this.invoices[invoiceIndex].pages.splice(pageIndex, 1)

            const input = document.querySelector(`[data-invoice-file-input="${this.invoices[invoiceIndex].id}"]`)

            if (input) {
                this.syncInvoiceFileInput(input, invoiceIndex)
            }
        },

        revokeInvoicePages(invoiceIndex) {
            const invoice = this.invoices[invoiceIndex]

            if (!invoice) return

            invoice.pages.forEach((page) => {
                if (page?.preview && !page?.is_existing) {
                    URL.revokeObjectURL(page.preview)
                }
            })
        },

        formatCurrency(value) {
            const number = Number(String(value || '').replace(/[^\d]/g, ''))

            if (!number) return ''

            return new Intl.NumberFormat('id-ID').format(number)
        },

        normalizeNominal(index) {
            const raw = String(this.invoices[index].nominal || '').replace(/[^\d]/g, '')
            this.invoices[index].nominal = raw
        },

        get totalNominal() {
            return this.invoices.reduce((total, invoice) => {
                const nominal = Number(String(invoice.nominal || '').replace(/[^\d]/g, ''))
                return total + (Number.isNaN(nominal) ? 0 : nominal)
            }, 0)
        },

        get totalPages() {
            return this.invoices.reduce((total, invoice) => {
                return total + invoice.pages.length
            }, 0)
        },

        get formattedTotalNominal() {
            return new Intl.NumberFormat('id-ID').format(this.totalNominal)
        },

        evaluationStatusLabel(status) {
            const labels = {
                unchecked: 'Belum dicek',
                matched: 'Sesuai',
                rejected: 'Ditolak',
                partially_rejected: 'Ditolak sebagian',
            }

            return labels[status] || status
        },

        evaluationStatusClass(status) {
            const classes = {
                unchecked: 'bg-gray-100 text-gray-700',
                matched: 'bg-green-100 text-green-700',
                rejected: 'bg-red-100 text-red-700',
                partially_rejected: 'bg-amber-100 text-amber-700',
            }

            return classes[status] || 'bg-gray-100 text-gray-700'
        },

        get resolvedEvaluationStatus() {
            const statuses = this.invoices
                .map((invoice) => invoice.evaluation_status)
                .filter(Boolean)

            const uniqueStatuses = [...new Set(statuses)]

            if (!uniqueStatuses.length) {
                return 'unchecked'
            }

            if (uniqueStatuses.includes('unchecked')) {
                return 'unchecked'
            }

            if (uniqueStatuses.length === 1) {
                return uniqueStatuses[0]
            }

            return 'partially_rejected'
        },

        get resolvedEvaluationStatusLabel() {
            const labels = {
                unchecked: 'Belum dicek',
                matched: 'Sesuai',
                rejected: 'Ditolak',
                partially_rejected: 'Ditolak sebagian',
            }

            return labels[this.resolvedEvaluationStatus] || this.resolvedEvaluationStatus
        },

        get resolvedEvaluationStatusClass() {
            const classes = {
                unchecked: 'bg-gray-100 text-gray-700',
                matched: 'bg-green-100 text-green-700',
                rejected: 'bg-red-100 text-red-700',
                partially_rejected: 'bg-amber-100 text-amber-700',
            }

            return classes[this.resolvedEvaluationStatus] || 'bg-gray-100 text-gray-700'
        },

        refreshSignaturePadsIfNeeded() {
            if (this.currentStep !== 3) return

            this.$nextTick(() => {
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('goods-receipt:signature-resize'))
                }, 80)
            })
        },

        submitAs(action) {
            if (this.isCompressing) {
                this.showWarning('Tunggu sampai proses compress foto selesai.')
                return
            }

            if (this.isSubmitting) {
                return
            }

            if (
                action === 'submit_approval' &&
                this.invoices.some((invoice) => invoice.evaluation_status === 'unchecked')
            ) {
                this.showWarning('Semua faktur harus sudah dicek sebelum diajukan untuk approval.')
                return
            }

            const validation = action === 'draft'
                ? { valid: true, step: null, errors: [] }
                : this.validateBeforeSubmit()

            if (!validation.valid) {
                this.currentStep = validation.step
                this.scrollToTop()

                if (validation.step === 3) {
                    this.refreshSignaturePadsIfNeeded()
                }

                this.showValidationErrors(validation.errors)

                return
            }

            const submitAction = document.querySelector('[name="submit_action"]')

            if (submitAction) {
                submitAction.value = action
            }

            const form = document.querySelector('[data-goods-receipt-form]')

            if (!form) {
                this.showWarning('Form penerimaan barang tidak ditemukan.')
                return
            }

            this.isSubmitting = true
            form.submit()
        },

        validateBeforeSubmit() {
            const errors = []

            const receivedDate = document.querySelector('[name="received_date"]')?.value?.trim()
            const supplierId = document.querySelector('[name="supplier_id"]')?.value?.trim()
            const branchId = document.querySelector('[name="branch_id"]')?.value?.trim()
            const spReferenceNumber = document.querySelector('[name="sp_reference_number"]')?.value?.trim()
            const receivedBy = document.querySelector('[name="received_by"]')?.value?.trim()
            const senderName = document.querySelector('[name="sender_name"]')?.value?.trim()

            // Step 1
            if (!receivedDate) {
                errors.push('Tanggal barang datang wajib diisi.')
            }

            if (!supplierId) {
                errors.push('Supplier wajib dipilih.')
            }

            if (!branchId) {
                errors.push('Cabang wajib dipilih.')
            }

            if (!spReferenceNumber) {
                errors.push('No referensi SP wajib diisi.')
            }

            if (!receivedBy) {
                errors.push('Nama penerima wajib dipilih.')
            }

            if (!senderName) {
                errors.push('Nama kurir / pengirim wajib diisi.')
            }

            if (errors.length > 0) {
                return {
                    valid: false,
                    step: 1,
                    errors,
                }
            }

            // Step 2
            if (!this.invoices.length) {
                return {
                    valid: false,
                    step: 2,
                    errors: ['Minimal harus ada 1 faktur.'],
                }
            }

            const invoiceErrors = []

            this.invoices.forEach((invoice, index) => {
                const invoiceNumber = String(invoice.invoice_number || '').trim()
                const invoiceDate = String(invoice.invoice_date || '').trim()
                const nominal = this.parseNominal(invoice.nominal)
                const totalPages = invoice.pages?.length || 0

                if (!invoiceNumber) {
                    invoiceErrors.push(`Nomor faktur ke-${index + 1} wajib diisi.`)
                }

                if (!invoiceDate) {
                    invoiceErrors.push(`Tanggal faktur ke-${index + 1} wajib diisi.`)
                }

                if (nominal === null || nominal < 0) {
                    invoiceErrors.push(`Nominal faktur ke-${index + 1} wajib diisi dengan angka yang valid.`)
                }

                if (totalPages < 1) {
                    invoiceErrors.push(`Foto faktur ke-${index + 1} wajib diupload minimal 1 halaman.`)
                }
            })

            if (invoiceErrors.length > 0) {
                return {
                    valid: false,
                    step: 2,
                    errors: invoiceErrors,
                }
            }

            // Step 3
            const evaluationErrors = []

            this.invoices.forEach((invoice, index) => {
                if (!invoice.evaluation_status) {
                    evaluationErrors.push(`Status evaluasi faktur ke-${index + 1} wajib dipilih.`)
                }
            })

            const senderSignatureInput = document.querySelector('[name="sender_signature"]')
            const senderSignature = senderSignatureInput?.value?.trim()

            const existingSenderSignature = Boolean(
                document.querySelector('[data-existing-sender-signature]')
            )

            if (!senderSignature && !existingSenderSignature) {
                evaluationErrors.push('Tanda tangan kurir / pengirim wajib diisi.')
            }

            if (evaluationErrors.length > 0) {
                return {
                    valid: false,
                    step: 3,
                    errors: evaluationErrors,
                }
            }

            return {
                valid: true,
                step: null,
                errors: [],
            }
        },

        parseNominal(value) {
            const normalized = String(value ?? '')
                .replace(/[^\d]/g, '')
                .trim()

            if (!normalized) {
                return null
            }

            const number = Number(normalized)

            return Number.isFinite(number) ? number : null
        },

        showValidationErrors(errors = []) {
            if (!errors.length) {
                return
            }

            if (!window.Swal) {
                alert(errors.join('\n'))
                return
            }

            Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap',
                html: `
                    <div style="text-align:left">
                        <p style="margin-bottom:8px">
                            Lengkapi data wajib berikut sebelum menyimpan:
                        </p>
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

        showWarning(message) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: message,
                })
                return
            }

            alert(message)
        },

        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            })
        },
    }))

    Alpine.data('signaturePad', (inputName) => ({
        inputName,
        canvas: null,
        context: null,
        isDrawing: false,
        hasSignature: false,
        lastX: 0,
        lastY: 0,

        init() {
            this.canvas = this.$refs.canvas
            this.context = this.canvas.getContext('2d')

            this.$nextTick(() => {
                setTimeout(() => {
                    this.resizeCanvas()
                }, 80)
            })

            window.addEventListener('resize', () => {
                this.resizeCanvas()
            })

            window.addEventListener('goods-receipt:signature-resize', () => {
                this.resizeCanvas()
            })
        },

        resizeCanvas() {
            if (!this.canvas) return

            const rect = this.canvas.getBoundingClientRect()

            // Kalau canvas masih hidden, jangan set ukuran 0.
            if (!rect.width || !rect.height) {
                return
            }

            const ratio = window.devicePixelRatio || 1

            const previousData = this.hasSignature
                ? this.canvas.toDataURL('image/png')
                : null

            this.canvas.width = Math.floor(rect.width * ratio)
            this.canvas.height = Math.floor(rect.height * ratio)

            this.context = this.canvas.getContext('2d')
            this.context.setTransform(ratio, 0, 0, ratio, 0, 0)

            this.context.lineWidth = 2.5
            this.context.lineCap = 'round'
            this.context.lineJoin = 'round'
            this.context.strokeStyle = '#111827'
            this.context.fillStyle = '#111827'

            if (previousData) {
                const image = new Image()

                image.onload = () => {
                    this.context.drawImage(image, 0, 0, rect.width, rect.height)
                    this.updateHiddenInput()
                }

                image.src = previousData
            }
        },

        getPoint(event) {
            const rect = this.canvas.getBoundingClientRect()

            let clientX
            let clientY

            if (event.touches && event.touches.length > 0) {
                clientX = event.touches[0].clientX
                clientY = event.touches[0].clientY
            } else if (event.changedTouches && event.changedTouches.length > 0) {
                clientX = event.changedTouches[0].clientX
                clientY = event.changedTouches[0].clientY
            } else {
                clientX = event.clientX
                clientY = event.clientY
            }

            return {
                x: clientX - rect.left,
                y: clientY - rect.top,
            }
        },

        startDrawing(event) {
            event.preventDefault()

            // Pastikan canvas sudah punya ukuran valid saat mulai gambar.
            if (!this.canvas.width || !this.canvas.height) {
                this.resizeCanvas()
            }

            const rect = this.canvas.getBoundingClientRect()

            if (!rect.width || !rect.height) {
                return
            }

            const point = this.getPoint(event)

            this.isDrawing = true
            this.hasSignature = true
            this.lastX = point.x
            this.lastY = point.y

            this.context.beginPath()
            this.context.arc(point.x, point.y, 1.5, 0, Math.PI * 2)
            this.context.fill()

            this.updateHiddenInput()
        },

        draw(event) {
            if (!this.isDrawing) return

            event.preventDefault()

            const point = this.getPoint(event)

            this.context.beginPath()
            this.context.moveTo(this.lastX, this.lastY)
            this.context.lineTo(point.x, point.y)
            this.context.stroke()

            this.lastX = point.x
            this.lastY = point.y

            this.updateHiddenInput()
        },

        stopDrawing() {
            if (!this.isDrawing) return

            this.isDrawing = false
            this.updateHiddenInput()
        },

        clear() {
            if (!this.canvas || !this.context) return

            const rect = this.canvas.getBoundingClientRect()

            this.context.clearRect(0, 0, rect.width, rect.height)

            this.hasSignature = false
            this.isDrawing = false

            const input = document.querySelector(`[name="${this.inputName}"]`)

            if (input) {
                input.value = ''
            }
        },

        updateHiddenInput() {
            if (!this.canvas || !this.hasSignature) return

            const input = document.querySelector(`[name="${this.inputName}"]`)

            if (input) {
                input.value = this.canvas.toDataURL('image/png')
            }
        },
    }))

    Alpine.data('searchableSelect', (config = {}) => ({
        open: false,
        search: '',
        selectedId: config.selectedId ? String(config.selectedId) : '',
        selectedLabel: config.selectedLabel || '',
        options: config.options || [],
        placeholder: config.placeholder || 'Pilih data',

        get filteredOptions() {
            const keyword = this.search.toLowerCase().trim()

            if (!keyword) {
                return this.options
            }

            return this.options.filter((option) =>
                String(option.label).toLowerCase().includes(keyword)
            )
        },

        choose(option) {
            this.selectedId = String(option.id)
            this.selectedLabel = option.label
            this.search = ''
            this.open = false
        },

        clear() {
            this.selectedId = ''
            this.selectedLabel = ''
            this.search = ''
        },

        init() {
            this.$watch('open', (isOpen) => {
                if (isOpen) {
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus()
                    })
                }
            })
        },
    }))
})
