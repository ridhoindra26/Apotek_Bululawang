document.addEventListener('alpine:init', () => {
    Alpine.data('multiUpload', (config = {}) => ({
        name: config.name || 'photos',   // base name, e.g. 'blood_check_photo'
        required: !!config.required,     // kalau suatu hari mau pakai
        max: config.max || null,         // optional: batasi jumlah input

        // setiap field = 1 input file + 1 preview
        fields: [
            { id: Date.now(), preview: null },
        ],

        addField() {
            if (this.max && this.fields.length >= this.max) return;

            this.fields.push({
                id: Date.now() + Math.random(),
                preview: null,
            });
        },

        removeField(index) {
            // jika required dan cuma 1 baris, jangan dihapus
            if (this.required && this.fields.length <= 1) {
                return;
            }

            const field = this.fields[index];
            if (field && field.preview && field.preview.startsWith('blob:')) {
                URL.revokeObjectURL(field.preview);
            }

            this.fields.splice(index, 1);
        },

        onFileChange(event, index) {
            const file = event.target.files[0];

            // kalau user clear file
            if (!file) {
                const prev = this.fields[index].preview;
                if (prev && prev.startsWith('blob:')) {
                    URL.revokeObjectURL(prev);
                }
                this.fields[index].preview = null;
                return;
            }

            // bersihkan preview lama
            if (this.fields[index].preview && this.fields[index].preview.startsWith('blob:')) {
                URL.revokeObjectURL(this.fields[index].preview);
            }

            this.fields[index].preview = URL.createObjectURL(file);

            // JIKA INI INPUT TERAKHIR → OTOMATIS TAMBAH SATU INPUT BARU
            const isLast = index === this.fields.length - 1;
            const canAddMore = !this.max || this.fields.length < this.max;

            if (isLast && canAddMore) {
                this.addField();
            }
        },
    }));
});