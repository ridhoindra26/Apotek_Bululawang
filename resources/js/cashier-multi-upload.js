document.addEventListener('alpine:init', () => {
    async function compressImage(file, {
        maxWidth = 1200,
        maxHeight = 1200,
        quality = 0.65,
        mimeType = 'image/jpeg',
    } = {}) {
        return new Promise((resolve, reject) => {
            if (!file || !file.type.startsWith('image/')) {
                resolve(file);
                return;
            }

            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(objectUrl);

                let width = img.width;
                let height = img.height;

                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            reject(new Error('Failed to compress image'));
                            return;
                        }

                        const newName = file.name.replace(/\.[^/.]+$/, '') + '.jpg';

                        const compressedFile = new File([blob], newName, {
                            type: mimeType,
                            lastModified: Date.now(),
                        });

                        resolve(compressedFile);
                    },
                    mimeType,
                    quality
                );
            };

            img.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Failed to load image'));
            };

            img.src = objectUrl;
        });
    }

    function replaceInputFile(input, file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
    }

    function formatSize(size) {
        if (!size) return '0 KB';

        const kb = size / 1024;

        if (kb < 1024) {
            return `${kb.toFixed(0)} KB`;
        }

        return `${(kb / 1024).toFixed(2)} MB`;
    }

    Alpine.data('singleUpload', () => ({
        preview: null,
        sizeText: null,
        loading: false,

        async onFileChange(event) {
            const input = event.target;
            const file = input.files[0];

            if (!file) {
                this.clearPreview();
                return;
            }

            try {
                this.loading = true;

                const compressedFile = await compressImage(file, {
                    maxWidth: 1200,
                    maxHeight: 1200,
                    quality: 0.65,
                });

                replaceInputFile(input, compressedFile);

                this.clearPreview();

                this.preview = URL.createObjectURL(compressedFile);
                this.sizeText = `${formatSize(file.size)} → ${formatSize(compressedFile.size)}`;
            } catch (error) {
                console.error(error);

                input.value = '';
                this.clearPreview();

                alert('Gagal mengecilkan ukuran foto. Silakan coba foto lain.');
            } finally {
                this.loading = false;
            }
        },

        clearPreview() {
            if (this.preview && this.preview.startsWith('blob:')) {
                URL.revokeObjectURL(this.preview);
            }

            this.preview = null;
            this.sizeText = null;
        },
    }));

    Alpine.data('multiUpload', (config = {}) => ({
        name: config.name || 'photos',
        required: !!config.required,
        max: config.max || null,

        fields: [
            {
                id: Date.now(),
                preview: null,
                sizeText: null,
                loading: false,
            },
        ],

        addField() {
            if (this.max && this.fields.length >= this.max) return;

            this.fields.push({
                id: Date.now() + Math.random(),
                preview: null,
                sizeText: null,
                loading: false,
            });
        },

        removeField(index) {
            if (this.required && this.fields.length <= 1) {
                return;
            }

            const field = this.fields[index];

            if (field && field.preview && field.preview.startsWith('blob:')) {
                URL.revokeObjectURL(field.preview);
            }

            this.fields.splice(index, 1);
        },

        async onFileChange(event, index) {
            const input = event.target;
            const file = input.files[0];

            if (!file) {
                this.clearField(index);
                return;
            }

            try {
                this.fields[index].loading = true;

                const compressedFile = await compressImage(file, {
                    maxWidth: 1200,
                    maxHeight: 1200,
                    quality: 0.65,
                });

                replaceInputFile(input, compressedFile);

                this.clearField(index);

                this.fields[index].preview = URL.createObjectURL(compressedFile);
                this.fields[index].sizeText = `${formatSize(file.size)} → ${formatSize(compressedFile.size)}`;

                const isLast = index === this.fields.length - 1;
                const canAddMore = !this.max || this.fields.length < this.max;

                if (isLast && canAddMore) {
                    this.addField();
                }
            } catch (error) {
                console.error(error);

                input.value = '';
                this.clearField(index);

                alert('Gagal mengecilkan ukuran foto. Silakan coba foto lain.');
            } finally {
                this.fields[index].loading = false;
            }
        },

        clearField(index) {
            const field = this.fields[index];

            if (!field) return;

            if (field.preview && field.preview.startsWith('blob:')) {
                URL.revokeObjectURL(field.preview);
            }

            field.preview = null;
            field.sizeText = null;
        },
    }));
});