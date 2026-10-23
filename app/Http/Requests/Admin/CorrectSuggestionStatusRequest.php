<?php

namespace App\Http\Requests\Admin;

use App\Models\Suggestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorrectSuggestionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Suggestion::STATUSES)],
            'correction_reason' => ['required', 'string', 'min:10', 'max:2000'],
            'rejection_reason' => ['nullable', 'string', 'max:10000', Rule::requiredIf($this->input('status') === Suggestion::STATUS_REJECTED)],
            'closure_reason' => ['nullable', 'string', 'max:10000', Rule::requiredIf($this->input('status') === Suggestion::STATUS_CLOSED)],
            'implementation_note' => ['nullable', 'string', 'max:10000', Rule::requiredIf($this->input('status') === Suggestion::STATUS_IMPLEMENTED)],
        ];
    }
}
