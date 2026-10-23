<?php

namespace App\Http\Requests\Admin;

use App\Models\SuggestionReward;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuggestionRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(SuggestionReward::TYPES)],
            'amount' => ['nullable', Rule::requiredIf($this->input('type') === SuggestionReward::TYPE_MONEY), 'numeric', 'gt:0', 'decimal:0,2'],
            'gift_description' => ['nullable', Rule::requiredIf($this->input('type') === SuggestionReward::TYPE_GIFT), 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
