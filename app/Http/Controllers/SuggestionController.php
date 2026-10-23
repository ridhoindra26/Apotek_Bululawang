<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuggestionRequest;
use App\Http\Requests\UpdateSuggestionRequest;
use App\Models\Branches;
use App\Models\Suggestion;
use App\Models\SuggestionAttachment;
use App\Services\SuggestionAttachmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class SuggestionController extends Controller
{
    public function __construct(private SuggestionAttachmentService $attachments) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Suggestion::class);

        $query = Suggestion::query()
            ->public()
            ->with(['submitter:id,name', 'targetBranch:id,name'])
            ->withCount('attachments');

        $this->applyFilters($query, $request);

        $suggestions = $query
            ->orderBy('submitted_at', $request->string('sort')->toString() === 'oldest' ? 'asc' : 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('suggestions.index', [
            'suggestions' => $suggestions,
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function mine(Request $request)
    {
        $employeeId = $request->user()->id_employee;
        abort_unless($employeeId, 403, 'Akun belum terhubung dengan data karyawan.');

        $suggestions = Suggestion::query()
            ->where('submitted_by_employee_id', $employeeId)
            ->with(['targetBranch:id,name', 'activeReward'])
            ->latest('submitted_at')
            ->paginate(12)
            ->withQueryString();

        return view('suggestions.mine', compact('suggestions'));
    }

    public function archive(Request $request)
    {
        $query = Suggestion::query()
            ->implemented()
            ->with(['submitter:id,name', 'targetBranch:id,name']);

        if (! $request->user()->hasRole('superadmin')) {
            $employeeId = $request->user()->id_employee;
            $query->where(function (Builder $query) use ($employeeId) {
                $query->where('visibility', Suggestion::VISIBILITY_PUBLIC);
                if ($employeeId) {
                    $query->orWhere('submitted_by_employee_id', $employeeId);
                }
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('implementation_note', 'like', "%{$search}%"));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('branch_id')) {
            $request->string('branch_id')->toString() === 'general'
                ? $query->whereNull('target_branch_id')
                : $query->where('target_branch_id', $request->integer('branch_id'));
        }
        if ($request->filled('implemented_from')) {
            $query->whereDate('implemented_at', '>=', $request->date('implemented_from'));
        }
        if ($request->filled('implemented_to')) {
            $query->whereDate('implemented_at', '<=', $request->date('implemented_to'));
        }

        $suggestions = $query->latest('implemented_at')->paginate(12)->withQueryString();

        return view('suggestions.archive', [
            'suggestions' => $suggestions,
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Suggestion::class);

        return view('suggestions.create', [
            'suggestion' => new Suggestion(['visibility' => Suggestion::VISIBILITY_PUBLIC]),
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreSuggestionRequest $request)
    {
        $data = $request->safe()->except(['attachments']);
        $data['target_branch_id'] = $data['branch_id'] ?? null;
        unset($data['branch_id']);
        $employee = $request->user()->employee()->with('branches:id,name')->first();
        abort_unless($employee, 403, 'Data karyawan untuk akun ini tidak ditemukan.');

        $storedFiles = [];

        try {
            $suggestion = DB::transaction(function () use ($request, $data, $employee, &$storedFiles) {
                $suggestion = Suggestion::create(array_merge($data, [
                    'submitted_by_employee_id' => $employee->id,
                    'submitter_name_snapshot' => $employee->name,
                    'submitter_branch_name_snapshot' => $employee->branches?->name,
                    'status' => Suggestion::STATUS_SUBMITTED,
                    'submitted_at' => now(),
                ]));

                $storedFiles = $this->attachments->store($suggestion, $request->file('attachments', []));

                return $suggestion;
            });
        } catch (Throwable $exception) {
            $this->attachments->deleteStoredFiles($storedFiles);
            throw $exception;
        }

        return redirect()->route('suggestions.show', $suggestion)
            ->with('success', 'Saran berhasil diajukan.');
    }

    public function show(Suggestion $suggestion)
    {
        Gate::authorize('view', $suggestion);
        $suggestion->load(['submitter:id,name', 'targetBranch:id,name', 'attachments']);
        $user = request()->user();
        if ($user->hasRole('superadmin') || (int) $user->id_employee === (int) $suggestion->submitted_by_employee_id) {
            $suggestion->load(['activeReward', 'rewards' => fn ($query) => $query->latest()]);
        }

        return view('suggestions.show', compact('suggestion'));
    }

    public function edit(Suggestion $suggestion)
    {
        Gate::authorize('update', $suggestion);
        $suggestion->load('attachments');

        return view('suggestions.edit', [
            'suggestion' => $suggestion,
            'branches' => Branches::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateSuggestionRequest $request, Suggestion $suggestion)
    {
        $data = $request->safe()->except(['attachments', 'deleted_attachment_ids']);
        $data['target_branch_id'] = $data['branch_id'] ?? null;
        unset($data['branch_id']);
        $storedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($request, $suggestion, $data, &$storedFiles, &$filesToDelete) {
                $locked = Suggestion::query()->lockForUpdate()->findOrFail($suggestion->id);
                Gate::authorize('update', $locked);

                $deletedIds = collect($request->validated('deleted_attachment_ids', []))->map(fn ($id) => (int) $id);
                $deleted = $locked->attachments()->whereIn('id', $deletedIds)->get();
                $filesToDelete = $deleted->map(fn ($attachment) => [
                    'disk' => $attachment->disk,
                    'path' => $attachment->file_path,
                ])->all();
                $locked->attachments()->whereIn('id', $deletedIds)->delete();

                $remainingCount = $locked->attachments()->count();
                $newFiles = $request->file('attachments', []);
                if ($remainingCount + count($newFiles) > config('suggestions.attachments.max_files')) {
                    throw ValidationException::withMessages([
                        'attachments' => 'Total lampiran maksimal '.config('suggestions.attachments.max_files').' file.',
                    ]);
                }

                $locked->update($data);
                $storedFiles = $this->attachments->store($locked, $newFiles);
            });
        } catch (Throwable $exception) {
            $this->attachments->deleteStoredFiles($storedFiles);
            throw $exception;
        }

        $this->attachments->deleteStoredFiles($filesToDelete);

        return redirect()->route('suggestions.show', $suggestion)
            ->with('success', 'Saran berhasil diperbarui.');
    }

    public function downloadAttachment(SuggestionAttachment $attachment)
    {
        $attachment->loadMissing('suggestion');
        Gate::authorize('view', $attachment->suggestion);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->file_path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->file_path,
            $attachment->original_filename,
            ['Content-Type' => $attachment->mime_type]
        );
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('previous_condition', 'like', "%{$search}%")
                ->orWhere('proposed_solution', 'like', "%{$search}%")
                ->orWhere('expected_benefit', 'like', "%{$search}%"));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('branch_id')) {
            $query->where('target_branch_id', $request->integer('branch_id'));
        }
    }
}
