document.addEventListener('alpine:init', () => {
    Alpine.data('multiUpload', (config = {}) => ({
        name: config.name || 'photos',
        required: !!config.required,        
        max: config.max || null,

        fields: [
            { id: Date.now(), preview: null },
        ],

        addField() {
            if (this.max && this.fields.length >= this.max) {
                return;
            }

            this.fields.push({
                id: Date.now() + Math.random(),
                preview: null,
            });
        },

        removeField(index) {
            if (this.required && this.fields.length <= 1) return;

            const field = this.fields[index];
            if (field && field.preview && field.preview.startsWith('blob:')) {
                URL.revokeObjectURL(field.preview);
            }

            this.fields.splice(index, 1);
        },

        onFileChange(event, index) {
            const file = event.target.files[0];

            if (!file) {
                const prev = this.fields[index].preview;
                if (prev && prev.startsWith('blob:')) {
                    URL.revokeObjectURL(prev);
                }
                this.fields[index].preview = null;
                return;
            }

            if (this.fields[index].preview && this.fields[index].preview.startsWith('blob:')) {
                URL.revokeObjectURL(this.fields[index].preview);
            }

            this.fields[index].preview = URL.createObjectURL(file);
        },

        cleanup() {
            // Optional: call from x-init / beforeunload if you want
            this.fields.forEach((f) => {
                if (f.preview && f.preview.startsWith('blob:')) {
                    URL.revokeObjectURL(f.preview);
                }
            });
        },
    }));
});
