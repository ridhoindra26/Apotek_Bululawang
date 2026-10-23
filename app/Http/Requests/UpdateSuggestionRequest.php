<?php

namespace App\Http\Requests;

use App\Models\Suggestion;
use Illuminate\Validation\Rule;

class UpdateSuggestionRequest extends StoreSuggestionRequest
{
    public function authorize(): bool
    {
        $suggestion = $this->route('suggestion');

        return $suggestion instanceof Suggestion
            && ($this->user()?->can('update', $suggestion) ?? false);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'deleted_attachment_ids' => ['nullable', 'array'],
            'deleted_attachment_ids.*' => [
                'integer',
                Rule::exists('suggestion_attachments', 'id')->where(
                    fn ($query) => $query->where('suggestion_id', $this->route('suggestion')?->id)
                ),
            ],
        ]);
    }
}
