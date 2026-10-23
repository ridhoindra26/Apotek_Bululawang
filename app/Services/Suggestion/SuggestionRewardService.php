<?php

namespace App\Services\Suggestion;

use App\Models\Suggestion;
use App\Models\SuggestionReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuggestionRewardService
{
    public function create(Suggestion $suggestion, array $data, User $actor): SuggestionReward
    {
        return DB::transaction(function () use ($suggestion, $data, $actor) {
            $lockedSuggestion = Suggestion::query()->lockForUpdate()->findOrFail($suggestion->id);
            if (! $lockedSuggestion->submitted_by_employee_id || ! $lockedSuggestion->submitter()->exists()) {
                throw ValidationException::withMessages(['reward' => 'Reward tidak dapat dibuat karena data karyawan penerima sudah tidak tersedia.']);
            }
            if ($lockedSuggestion->rewards()->active()->exists()) {
                throw ValidationException::withMessages(['reward' => 'Suggestion ini sudah memiliki reward aktif.']);
            }

            return $lockedSuggestion->rewards()->create($this->rewardAttributes($data) + [
                'status' => SuggestionReward::STATUS_PENDING,
                'created_by' => $actor->id,
            ]);
        });
    }

    public function update(SuggestionReward $reward, array $data): SuggestionReward
    {
        return DB::transaction(function () use ($reward, $data) {
            $locked = SuggestionReward::query()->lockForUpdate()->findOrFail($reward->id);
            $this->ensurePending($locked, 'Hanya reward yang menunggu penyerahan dapat diedit.');
            $locked->update($this->rewardAttributes($data));

            return $locked->fresh();
        });
    }

    public function deliver(SuggestionReward $reward, User $actor): SuggestionReward
    {
        return DB::transaction(function () use ($reward, $actor) {
            $locked = SuggestionReward::query()->lockForUpdate()->findOrFail($reward->id);
            $this->ensurePending($locked, 'Reward ini tidak dapat ditandai diserahkan.');
            $locked->update([
                'status' => SuggestionReward::STATUS_DELIVERED,
                'delivered_at' => now(),
                'delivered_by' => $actor->id,
            ]);

            return $locked->fresh();
        });
    }

    public function cancel(SuggestionReward $reward, string $reason, User $actor): SuggestionReward
    {
        return DB::transaction(function () use ($reward, $reason, $actor) {
            $locked = SuggestionReward::query()->lockForUpdate()->findOrFail($reward->id);
            $this->ensurePending($locked, 'Hanya reward yang menunggu penyerahan dapat dibatalkan.');
            $locked->update([
                'status' => SuggestionReward::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $actor->id,
                'cancellation_reason' => $reason,
            ]);

            return $locked->fresh();
        });
    }

    private function rewardAttributes(array $data): array
    {
        $money = $data['type'] === SuggestionReward::TYPE_MONEY;

        return [
            'type' => $data['type'],
            'amount' => $money ? $data['amount'] : null,
            'gift_description' => $money ? null : $data['gift_description'],
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function ensurePending(SuggestionReward $reward, string $message): void
    {
        if (! $reward->isPending()) {
            throw ValidationException::withMessages(['reward' => $message]);
        }
    }
}
