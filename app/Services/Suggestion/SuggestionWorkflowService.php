<?php

namespace App\Services\Suggestion;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuggestionWorkflowService
{
    public function transition(Suggestion $suggestion, string $nextStatus, array $context, User $actor): Suggestion
    {
        return DB::transaction(function () use ($suggestion, $nextStatus, $context, $actor) {
            $locked = Suggestion::query()->lockForUpdate()->findOrFail($suggestion->id);

            if (! $locked->canTransitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'status' => "Perubahan status dari {$locked->status} ke {$nextStatus} tidak diizinkan.",
                ]);
            }

            $this->validateRequiredContext($nextStatus, $context);
            $locked->fill($this->transitionAttributes($locked, $nextStatus, $context, $actor));
            $locked->save();

            return $locked->fresh();
        });
    }

    public function correct(Suggestion $suggestion, string $targetStatus, array $context, User $actor): Suggestion
    {
        return DB::transaction(function () use ($suggestion, $targetStatus, $context, $actor) {
            $locked = Suggestion::query()->lockForUpdate()->findOrFail($suggestion->id);

            if (! in_array($targetStatus, Suggestion::STATUSES, true) || $targetStatus === $locked->status) {
                throw ValidationException::withMessages([
                    'status' => 'Status koreksi harus valid dan berbeda dari status saat ini.',
                ]);
            }

            $this->validateRequiredContext($targetStatus, $context);
            $before = $locked->status;
            $attributes = $this->reconciledCorrectionAttributes($locked, $targetStatus, $context, $actor);
            $attributes += [
                'status_before_correction' => $before,
                'status_after_correction' => $targetStatus,
                'last_status_correction_reason' => $context['correction_reason'],
                'status_corrected_at' => now(),
                'status_corrected_by' => $actor->id,
            ];

            $locked->fill($attributes)->save();

            return $locked->fresh();
        });
    }

    private function validateRequiredContext(string $status, array $context): void
    {
        $messages = [];
        foreach ((new Suggestion)->requiredFieldsForTransition($status) as $field) {
            if (blank($context[$field] ?? null)) {
                $messages[$field] = match ($field) {
                    'rejection_reason' => 'Alasan penolakan wajib diisi.',
                    'closure_reason' => 'Alasan penutupan wajib diisi.',
                    'implementation_note' => 'Catatan implementasi wajib diisi.',
                };
            }
        }
        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function transitionAttributes(Suggestion $suggestion, string $status, array $context, User $actor): array
    {
        $now = now();
        $attributes = ['status' => $status, 'reviewed_by' => $actor->id];

        return match ($status) {
            Suggestion::STATUS_UNDER_REVIEW => $attributes + ['review_started_at' => $suggestion->review_started_at ?? $now],
            Suggestion::STATUS_ACCEPTED => $attributes + [
                'review_started_at' => $suggestion->review_started_at ?? $now,
                'decided_at' => $suggestion->decided_at ?? $now,
                'accepted_at' => $suggestion->accepted_at ?? $now,
            ],
            Suggestion::STATUS_REJECTED => $attributes + [
                'review_started_at' => $suggestion->review_started_at ?? $now,
                'decided_at' => $suggestion->decided_at ?? $now,
                'rejected_at' => $now,
                'rejection_reason' => $context['rejection_reason'],
            ],
            Suggestion::STATUS_PLANNED => $attributes + ['planned_at' => $now],
            Suggestion::STATUS_IN_PROGRESS => $attributes + ['in_progress_at' => $now],
            Suggestion::STATUS_IMPLEMENTED => $attributes + [
                'implemented_at' => $now,
                'implementation_note' => $context['implementation_note'],
            ],
            Suggestion::STATUS_CLOSED => $attributes + [
                'closed_at' => $now,
                'closure_reason' => $context['closure_reason'],
            ],
            default => $attributes,
        };
    }

    private function reconciledCorrectionAttributes(Suggestion $suggestion, string $status, array $context, User $actor): array
    {
        $attributes = $this->transitionAttributes($suggestion, $status, $context, $actor);
        $rank = array_flip([
            Suggestion::STATUS_SUBMITTED,
            Suggestion::STATUS_UNDER_REVIEW,
            Suggestion::STATUS_ACCEPTED,
            Suggestion::STATUS_PLANNED,
            Suggestion::STATUS_IN_PROGRESS,
            Suggestion::STATUS_IMPLEMENTED,
        ]);
        $targetRank = $rank[$status] ?? null;

        if ($targetRank !== null) {
            foreach (['review_started_at' => 1, 'decided_at' => 2, 'accepted_at' => 2, 'planned_at' => 3, 'in_progress_at' => 4, 'implemented_at' => 5] as $field => $fieldRank) {
                if ($fieldRank > $targetRank) {
                    $attributes[$field] = null;
                } elseif ($fieldRank <= $targetRank && $suggestion->{$field} === null) {
                    $attributes[$field] = now();
                }
            }
            $attributes['rejected_at'] = null;
            $attributes['closed_at'] = null;
        }

        if ($status === Suggestion::STATUS_REJECTED) {
            $attributes += ['accepted_at' => null, 'planned_at' => null, 'in_progress_at' => null, 'implemented_at' => null, 'closed_at' => null];
        }
        if ($status === Suggestion::STATUS_CLOSED) {
            $attributes += ['implemented_at' => null, 'rejected_at' => null];
        }

        if ($status !== Suggestion::STATUS_REJECTED) {
            $attributes['rejected_at'] = null;
            $attributes['rejection_reason'] = null;
        }
        if ($status !== Suggestion::STATUS_CLOSED) {
            $attributes['closed_at'] = null;
            $attributes['closure_reason'] = null;
        }
        if ($status !== Suggestion::STATUS_IMPLEMENTED) {
            $attributes['implementation_note'] = null;
        }
        if ($status === Suggestion::STATUS_SUBMITTED) {
            $attributes['reviewed_by'] = null;
        }

        return $attributes;
    }
}
