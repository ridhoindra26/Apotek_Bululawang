<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuggestionDashboardRequest;
use App\Models\Branches;
use App\Services\Suggestion\SuggestionAnalyticsService;

class SuggestionDashboardController extends Controller
{
    public function __construct(private SuggestionAnalyticsService $analytics) {}

    public function __invoke(SuggestionDashboardRequest $request)
    {
        $filters = $request->validated() + ['period' => 'year'];

        return view('admin.suggestions.dashboard', [
            'analytics' => $this->analytics->dashboard($filters),
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}
