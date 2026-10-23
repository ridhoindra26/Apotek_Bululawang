<?php

namespace App\Http\Requests\Admin;

use App\Models\Branches;
use App\Models\Suggestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuggestionDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::in(['month', '3months', '6months', 'year', 'all', 'custom'])],
            'branch_id' => ['nullable', Rule::in(array_merge(['general'], Branches::query()->pluck('id')->map(fn ($id) => (string) $id)->all()))],
            'type' => ['nullable', Rule::in(Suggestion::TYPES)],
            'date_from' => ['nullable', 'required_if:period,custom', 'date'],
            'date_to' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:date_from'],
        ];
    }
}
