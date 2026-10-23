<?php

namespace App\Services\Suggestion;

use App\Models\Branches;
use App\Models\Suggestion;
use App\Models\SuggestionReward;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SuggestionAnalyticsService
{
    public function dashboard(array $filters): array
    {
        [$from, $to, $periodLabel] = $this->period($filters);
        $cohort = $this->suggestions($filters, $from, $to);
        $operational = $this->suggestions($filters, null, null);

        $status = $cohort->clone()->select('status', DB::raw('COUNT(*) total'))->groupBy('status')->pluck('total', 'status');
        $types = $cohort->clone()->select('type', DB::raw('COUNT(*) total'))->groupBy('type')->pluck('total', 'type');
        $total = (int) $status->sum();
        $accepted = (int) $cohort->clone()->whereNotNull('accepted_at')->count();
        $rejected = (int) $cohort->clone()->where(function (Builder $q) {
            $q->whereNotNull('rejected_at')->orWhere('status', Suggestion::STATUS_REJECTED);
        })->count();
        $implemented = (int) ($status[Suggestion::STATUS_IMPLEMENTED] ?? 0);

        return [
            'period_label' => $periodLabel,
            'kpi' => $this->kpi($status, $total),
            'attention' => $this->attention($operational, $filters),
            'trend' => $this->monthlyTrend($cohort, $from, $to),
            'status' => $this->distribution(Suggestion::STATUSES, $status, $total),
            'types' => $this->distribution(Suggestion::TYPES, $types, $total),
            'branches' => $this->branchStats($cohort),
            'acceptance_rate' => ($accepted + $rejected) > 0 ? round($accepted / ($accepted + $rejected) * 100, 1) : null,
            'acceptance_basis' => $accepted + $rejected,
            'implementation_rate' => $total > 0 ? round($implemented / $total * 100, 1) : null,
            'average_review_time' => $this->averageReviewTime($cohort),
            'rewards' => $this->rewardSummary($filters, $from, $to),
            'recent_implemented' => $operational->clone()->where('status', Suggestion::STATUS_IMPLEMENTED)
                ->with(['submitter:id,name', 'targetBranch:id,name'])->latest('implemented_at')->limit(8)->get(),
        ];
    }

    private function suggestions(array $filters, ?Carbon $from, ?Carbon $to): Builder
    {
        $query = Suggestion::query();
        if (($filters['branch_id'] ?? null) === 'general') {
            $query->whereNull('target_branch_id');
        } elseif (filled($filters['branch_id'] ?? null)) {
            $query->where('target_branch_id', $filters['branch_id']);
        }
        if (filled($filters['type'] ?? null)) {
            $query->where('type', $filters['type']);
        }
        if ($from) {
            $query->where('submitted_at', '>=', $from);
        }
        if ($to) {
            $query->where('submitted_at', '<=', $to);
        }

        return $query;
    }

    private function period(array $filters): array
    {
        $now = now();

        return match ($filters['period'] ?? 'year') {
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'Bulan ini'],
            '3months' => [$now->copy()->subMonths(2)->startOfMonth(), $now->copy()->endOfMonth(), '3 bulan terakhir'],
            '6months' => [$now->copy()->subMonths(5)->startOfMonth(), $now->copy()->endOfMonth(), '6 bulan terakhir'],
            'all' => [null, null, 'Semua waktu'],
            'custom' => [Carbon::parse($filters['date_from'])->startOfDay(), Carbon::parse($filters['date_to'])->endOfDay(), 'Periode kustom'],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'Tahun ini'],
        };
    }

    private function kpi($status, int $total): array
    {
        $count = fn (string $key) => (int) ($status[$key] ?? 0);

        return ['total' => $total, 'pending_review' => $count('submitted') + $count('under_review'), 'submitted' => $count('submitted'), 'under_review' => $count('under_review'), 'accepted' => $count('accepted'), 'planned' => $count('planned'), 'in_progress' => $count('in_progress'), 'implemented' => $count('implemented'), 'closed_rejected' => $count('closed') + $count('rejected')];
    }

    private function attention(Builder $query, array $filters): array
    {
        $with = ['submitter:id,name', 'targetBranch:id,name'];
        $base = fn () => $query->clone()->with($with);
        $rewardQuery = SuggestionReward::query()->where('status', SuggestionReward::STATUS_PENDING)->with(['suggestion.submitter:id,name', 'suggestion.targetBranch:id,name']);
        if (($filters['branch_id'] ?? null) === 'general') {
            $rewardQuery->whereHas('suggestion', fn ($q) => $q->whereNull('target_branch_id'));
        } elseif (filled($filters['branch_id'] ?? null)) {
            $rewardQuery->whereHas('suggestion', fn ($q) => $q->where('target_branch_id', $filters['branch_id']));
        }
        if (filled($filters['type'] ?? null)) {
            $rewardQuery->whereHas('suggestion', fn ($q) => $q->where('type', $filters['type']));
        }

        return [
            'new' => $base()->where('status', 'submitted')->oldest('submitted_at')->limit(5)->get(),
            'review' => $base()->where('status', 'under_review')->oldest('review_started_at')->limit(5)->get(),
            'priority' => $base()->whereIn('priority', ['high', 'critical'])->whereNotIn('status', Suggestion::TERMINAL_STATUSES)->oldest('submitted_at')->limit(5)->get(),
            'planned' => $base()->where('status', 'planned')->oldest('planned_at')->limit(5)->get(),
            'progress' => $base()->where('status', 'in_progress')->oldest('in_progress_at')->limit(5)->get(),
            'rewards' => $rewardQuery->oldest()->limit(5)->get(),
        ];
    }

    private function monthlyTrend(Builder $query, ?Carbon $from, ?Carbon $to): array
    {
        $firstSubmission = Suggestion::query()->min('submitted_at');
        $start = ($from ?: ($firstSubmission ? Carbon::parse($firstSubmission) : now()))->copy()->startOfMonth();
        $end = ($to ?: now())->copy()->endOfMonth();
        $format = DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', submitted_at)" : "DATE_FORMAT(submitted_at, '%Y-%m')";
        $rows = $query->clone()->whereBetween('submitted_at', [$start, $end])->selectRaw("{$format} period, type, COUNT(*) total")->groupBy('period', 'type')->get();
        $labels = [];
        $series = array_fill_keys(Suggestion::TYPES, []);
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addMonth()) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->translatedFormat('M Y');
            foreach (Suggestion::TYPES as $type) {
                $series[$type][] = (int) optional($rows->first(fn ($r) => $r->period === $key && $r->type === $type))->total;
            }
        }

        return compact('labels', 'series');
    }

    private function distribution(array $keys, $values, int $total): array
    {
        return collect($keys)->map(fn ($key) => ['key' => $key, 'total' => (int) ($values[$key] ?? 0), 'percentage' => $total ? round(($values[$key] ?? 0) / $total * 100, 1) : 0])->all();
    }

    private function branchStats(Builder $query): array
    {
        $rows = $query->clone()->selectRaw("target_branch_id, COUNT(*) total, SUM(CASE WHEN accepted_at IS NOT NULL THEN 1 ELSE 0 END) accepted, SUM(CASE WHEN status='implemented' THEN 1 ELSE 0 END) implemented, SUM(CASE WHEN status IN ('submitted','under_review') THEN 1 ELSE 0 END) pending")->groupBy('target_branch_id')->get()->keyBy(fn ($r) => (string) ($r->target_branch_id ?? 'general'));

        return collect([['id' => 'general', 'name' => 'Seluruh Perusahaan']])->concat(Branches::orderBy('name')->get()->map(fn ($b) => ['id' => (string) $b->id, 'name' => $b->name]))->map(function ($branch) use ($rows) {
            $row = $rows[$branch['id']] ?? null;

            return $branch + ['total' => (int) ($row->total ?? 0), 'accepted' => (int) ($row->accepted ?? 0), 'implemented' => (int) ($row->implemented ?? 0), 'pending' => (int) ($row->pending ?? 0)];
        })->all();
    }

    private function averageReviewTime(Builder $query): ?string
    {
        $seconds = $query->clone()->whereNotNull('decided_at')->selectRaw(DB::connection()->getDriverName() === 'sqlite' ? 'AVG((julianday(decided_at)-julianday(submitted_at))*86400) average_seconds' : 'AVG(TIMESTAMPDIFF(SECOND, submitted_at, decided_at)) average_seconds')->value('average_seconds');
        if ($seconds === null) {
            return null;
        }

        return $seconds < 86400 ? round($seconds / 3600, 1).' jam' : round($seconds / 86400, 1).' hari';
    }

    private function rewardSummary(array $filters, ?Carbon $from, ?Carbon $to): array
    {
        $query = SuggestionReward::query();
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        if (($filters['branch_id'] ?? null) === 'general') {
            $query->whereHas('suggestion', fn ($q) => $q->whereNull('target_branch_id'));
        } elseif (filled($filters['branch_id'] ?? null)) {
            $query->whereHas('suggestion', fn ($q) => $q->where('target_branch_id', $filters['branch_id']));
        }
        if (filled($filters['type'] ?? null)) {
            $query->whereHas('suggestion', fn ($q) => $q->where('type', $filters['type']));
        }
        $counts = $query->clone()->selectRaw("COUNT(*) total, SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending, SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) delivered, SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled")->first();
        $delivered = SuggestionReward::query()->where('status', 'delivered')->where('type', 'money');
        if ($from) {
            $delivered->where('delivered_at', '>=', $from);
        }
        if ($to) {
            $delivered->where('delivered_at', '<=', $to);
        }
        if (($filters['branch_id'] ?? null) === 'general') {
            $delivered->whereHas('suggestion', fn ($q) => $q->whereNull('target_branch_id'));
        } elseif (filled($filters['branch_id'] ?? null)) {
            $delivered->whereHas('suggestion', fn ($q) => $q->where('target_branch_id', $filters['branch_id']));
        }
        if (filled($filters['type'] ?? null)) {
            $delivered->whereHas('suggestion', fn ($q) => $q->where('type', $filters['type']));
        }

        return ['total' => (int) $counts->total, 'pending' => (int) $counts->pending, 'delivered' => (int) $counts->delivered, 'cancelled' => (int) $counts->cancelled, 'delivered_money' => (float) $delivered->sum('amount')];
    }
}
