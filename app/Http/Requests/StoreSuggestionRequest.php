<?php

namespace App\Http\Requests;

use App\Models\Suggestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Suggestion::class) ?? false;
    }

    public function rules(): array
    {
        $contentRequirement = $this->input('type') === Suggestion::TYPE_COMPLAINT ? 'nullable' : 'required';

        return [
            'type' => ['required', Rule::in(Suggestion::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'visibility' => ['required', Rule::in(Suggestion::VISIBILITIES)],
            'previous_condition' => ['required', 'string', 'max:10000'],
            'proposed_solution' => [$contentRequirement, 'string', 'max:10000'],
            'expected_benefit' => [$contentRequirement, 'string', 'max:10000'],
            'additional_notes' => ['nullable', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array', 'max:'.config('suggestions.attachments.max_files')],
            'attachments.*' => [
                'file',
                'extensions:jpg,jpeg,png,webp,pdf',
                'max:'.config('suggestions.attachments.max_size_kb'),
                'mimetypes:'.implode(',', config('suggestions.attachments.mimetypes')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'proposed_solution.required' => 'Solusi yang diusulkan wajib diisi untuk tipe Saran dan Improvement.',
            'expected_benefit.required' => 'Manfaat yang diharapkan wajib diisi untuk tipe Saran dan Improvement.',
            'attachments.max' => 'Maksimal '.config('suggestions.attachments.max_files').' lampiran.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 5 MB.',
            'attachments.*.file' => 'Lampiran gagal diunggah. Pastikan file valid dan ukurannya maksimal 5 MB.',
            'attachments.*.extensions' => 'Ekstensi lampiran harus JPG, JPEG, PNG, WebP, atau PDF.',
            'attachments.*.mimetypes' => 'Lampiran harus berupa JPG, PNG, WebP, atau PDF.',
        ];
    }
}
