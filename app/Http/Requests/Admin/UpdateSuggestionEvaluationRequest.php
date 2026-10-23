<?php

namespace App\Http\Requests\Admin;

use App\Models\Suggestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuggestionEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    public function rules(): array
    {
        return [
            'impact' => ['nullable', Rule::in(Suggestion::IMPACTS)],
            'effort' => ['nullable', Rule::in(Suggestion::EFFORTS)],
            'priority' => ['nullable', Rule::in(Suggestion::PRIORITIES)],
        ];
    }
}
