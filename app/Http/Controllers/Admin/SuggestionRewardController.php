<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelSuggestionRewardRequest;
use App\Http\Requests\Admin\StoreSuggestionRewardRequest;
use App\Http\Requests\Admin\UpdateSuggestionRewardRequest;
use App\Models\Branches;
use App\Models\Suggestion;
use App\Models\SuggestionReward;
use App\Services\Suggestion\SuggestionRewardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SuggestionRewardController extends Controller
{
    public function __construct(private SuggestionRewardService $rewards) {}

    public function index(Request $request)
    {
        $query = SuggestionReward::query()->with(['suggestion.submitter:id,name', 'suggestion.targetBranch:id,name', 'creator:id,name']);
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->whereHas('suggestion', fn (Builder $q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('submitter_name_snapshot', 'like', "%{$search}%")
                ->orWhereHas('submitter', fn (Builder $e) => $e->where('name', 'like', "%{$search}%")));
        }
        foreach (['type', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->string($field)->toString());
            }
        }
        if ($request->filled('branch_id')) {
            $query->whereHas('suggestion', fn (Builder $q) => $q->where('target_branch_id', $request->integer('branch_id')));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $summary = SuggestionReward::query()->selectRaw('COUNT(*) total')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) pending")
            ->selectRaw("SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) delivered")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) cancelled")
            ->selectRaw("SUM(CASE WHEN status = 'delivered' AND type = 'money' THEN amount ELSE 0 END) delivered_money")
            ->first();

        return view('admin.suggestion-rewards.index', [
            'rewards' => $query->latest()->paginate(15)->withQueryString(),
            'summary' => $summary,
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
            'suggestionOptions' => Suggestion::query()->whereNotNull('submitted_by_employee_id')->whereHas('submitter')->doesntHave('activeReward')->latest('submitted_at')->limit(100)->get(['id', 'title', 'submitter_name_snapshot', 'status']),
        ]);
    }

    public function store(StoreSuggestionRewardRequest $request, Suggestion $suggestion)
    {
        $this->rewards->create($suggestion, $request->validated(), $request->user());

        return back()->with('success', 'Reward berhasil dibuat dan menunggu penyerahan.');
    }

    public function update(UpdateSuggestionRewardRequest $request, SuggestionReward $reward)
    {
        $this->rewards->update($reward, $request->validated());

        return back()->with('success', 'Reward berhasil diperbarui.');
    }

    public function deliver(Request $request, SuggestionReward $reward)
    {
        $this->rewards->deliver($reward, $request->user());

        return back()->with('success', 'Reward ditandai sudah diserahkan.');
    }

    public function cancel(CancelSuggestionRewardRequest $request, SuggestionReward $reward)
    {
        $this->rewards->cancel($reward, $request->validated('cancellation_reason'), $request->user());

        return back()->with('success', 'Reward dibatalkan dan tetap disimpan dalam histori.');
    }
}
