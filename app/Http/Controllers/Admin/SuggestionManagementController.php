<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CorrectSuggestionStatusRequest;
use App\Http\Requests\Admin\UpdateSuggestionEvaluationRequest;
use App\Http\Requests\Admin\UpdateSuggestionStatusRequest;
use App\Models\Branches;
use App\Models\Suggestion;
use App\Services\Suggestion\SuggestionWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuggestionManagementController extends Controller
{
    public function __construct(private SuggestionWorkflowService $workflow) {}

    public function index(Request $request)
    {
        $query = Suggestion::query()->with(['submitter:id,name', 'targetBranch:id,name']);
        $this->applyFilters($query, $request);

        $suggestions = $query
            ->orderBy('submitted_at', $request->string('sort')->toString() === 'oldest' ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.suggestions.index', [
            'suggestions' => $suggestions,
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Suggestion $suggestion)
    {
        $suggestion->load(['submitter:id,name', 'targetBranch:id,name', 'attachments', 'reviewer:id,name', 'statusCorrector:id,name', 'rewards' => fn ($query) => $query->latest()]);

        return view('admin.suggestions.show', [
            'suggestion' => $suggestion,
            'allowedStatuses' => Suggestion::ALLOWED_TRANSITIONS[$suggestion->status] ?? [],
            'recommendation' => $this->recommendation($suggestion->impact, $suggestion->effort),
        ]);
    }

    public function evaluation(UpdateSuggestionEvaluationRequest $request, Suggestion $suggestion)
    {
        DB::transaction(function () use ($request, $suggestion) {
            Suggestion::query()->lockForUpdate()->findOrFail($suggestion->id)->update($request->validated());
        });

        return back()->with('success', 'Evaluasi berhasil disimpan.');
    }

    public function status(UpdateSuggestionStatusRequest $request, Suggestion $suggestion)
    {
        $this->workflow->transition($suggestion, $request->validated('status'), $request->validated(), $request->user());

        return back()->with('success', 'Status suggestion berhasil diperbarui.');
    }

    public function correction(CorrectSuggestionStatusRequest $request, Suggestion $suggestion)
    {
        $this->workflow->correct($suggestion, $request->validated('status'), $request->validated(), $request->user());

        return back()->with('success', 'Koreksi administratif berhasil dicatat.');
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $quickStatuses = [
            'pending' => [Suggestion::STATUS_SUBMITTED, Suggestion::STATUS_UNDER_REVIEW],
            'new' => [Suggestion::STATUS_SUBMITTED],
            'review' => [Suggestion::STATUS_UNDER_REVIEW],
            'accepted' => [Suggestion::STATUS_ACCEPTED],
            'planned' => [Suggestion::STATUS_PLANNED],
            'progress' => [Suggestion::STATUS_IN_PROGRESS],
            'completed' => [Suggestion::STATUS_IMPLEMENTED],
            'closed' => [Suggestion::STATUS_CLOSED, Suggestion::STATUS_REJECTED],
        ];
        if (isset($quickStatuses[$request->string('tab')->toString()])) {
            $query->whereIn('status', $quickStatuses[$request->string('tab')->toString()]);
        }
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(fn (Builder $q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('previous_condition', 'like', "%{$search}%")
                ->orWhere('proposed_solution', 'like', "%{$search}%")
                ->orWhere('submitter_name_snapshot', 'like', "%{$search}%")
                ->orWhereHas('submitter', fn (Builder $employee) => $employee->where('name', 'like', "%{$search}%")));
        }
        foreach (['status', 'type', 'visibility', 'priority', 'impact', 'effort'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->string($field)->toString());
            }
        }
        if ($request->filled('branch_id')) {
            $query->where('target_branch_id', $request->integer('branch_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date('date_to'));
        }
    }

    private function recommendation(?string $impact, ?string $effort): ?string
    {
        return [
            'high.low' => 'Quick Win', 'high.medium' => 'Strategic Opportunity', 'high.high' => 'Major Project',
            'medium.low' => 'Quick Win', 'medium.medium' => 'Evaluate', 'medium.high' => 'Low Priority',
            'low.low' => 'Minor Improvement', 'low.medium' => 'Low Priority', 'low.high' => 'Reconsider',
        ][$impact.'.'.$effort] ?? null;
    }
}
