<?php

namespace App\Policies;

use App\Models\Suggestion;
use App\Models\User;

class SuggestionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Suggestion $suggestion): bool
    {
        return $suggestion->visibility === Suggestion::VISIBILITY_PUBLIC
            || $user->hasRole('superadmin')
            || ($user->id_employee && (int) $suggestion->submitted_by_employee_id === (int) $user->id_employee);
    }

    public function create(User $user): bool
    {
        return ! empty($user->id_employee);
    }

    public function update(User $user, Suggestion $suggestion): bool
    {
        return ! empty($user->id_employee)
            && (int) $suggestion->submitted_by_employee_id === (int) $user->id_employee
            && $suggestion->isEditableBySubmitter();
    }
}
