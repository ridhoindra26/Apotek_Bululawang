<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suggestion extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_SUGGESTION = 'suggestion';

    public const TYPE_COMPLAINT = 'complaint';

    public const TYPE_IMPROVEMENT = 'improvement';

    public const TYPES = [self::TYPE_SUGGESTION, self::TYPE_COMPLAINT, self::TYPE_IMPROVEMENT];

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITIES = [self::VISIBILITY_PUBLIC, self::VISIBILITY_PRIVATE];

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PLANNED = 'planned';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_IMPLEMENTED = 'implemented';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_PLANNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_IMPLEMENTED,
        self::STATUS_CLOSED,
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_REJECTED,
        self::STATUS_IMPLEMENTED,
        self::STATUS_CLOSED,
    ];

    public const EDITABLE_BY_SUBMITTER_STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
    ];

    public const ALLOWED_TRANSITIONS = [
        self::STATUS_SUBMITTED => [self::STATUS_UNDER_REVIEW, self::STATUS_ACCEPTED, self::STATUS_REJECTED],
        self::STATUS_UNDER_REVIEW => [self::STATUS_ACCEPTED, self::STATUS_REJECTED],
        self::STATUS_ACCEPTED => [self::STATUS_PLANNED, self::STATUS_CLOSED],
        self::STATUS_PLANNED => [self::STATUS_IN_PROGRESS, self::STATUS_CLOSED],
        self::STATUS_IN_PROGRESS => [self::STATUS_IMPLEMENTED, self::STATUS_CLOSED],
        self::STATUS_REJECTED => [],
        self::STATUS_IMPLEMENTED => [],
        self::STATUS_CLOSED => [],
    ];

    public const TRANSITION_REQUIRED_FIELDS = [
        self::STATUS_REJECTED => ['rejection_reason'],
        self::STATUS_CLOSED => ['closure_reason'],
        self::STATUS_IMPLEMENTED => ['implementation_note'],
    ];

    public const IMPACTS = ['low', 'medium', 'high'];

    public const EFFORTS = ['low', 'medium', 'high'];

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'submitted_by_employee_id',
        'submitter_name_snapshot',
        'submitter_branch_name_snapshot',
        'target_branch_id',
        'type',
        'visibility',
        'title',
        'previous_condition',
        'proposed_solution',
        'expected_benefit',
        'additional_notes',
        'status',
        'impact',
        'effort',
        'priority',
        'rejection_reason',
        'closure_reason',
        'implementation_note',
        'submitted_at',
        'review_started_at',
        'decided_at',
        'accepted_at',
        'implemented_at',
        'planned_at',
        'in_progress_at',
        'rejected_at',
        'closed_at',
        'reviewed_by',
        'last_status_correction_reason',
        'status_before_correction',
        'status_after_correction',
        'status_corrected_at',
        'status_corrected_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'review_started_at' => 'datetime',
        'decided_at' => 'datetime',
        'accepted_at' => 'datetime',
        'implemented_at' => 'datetime',
        'planned_at' => 'datetime',
        'in_progress_at' => 'datetime',
        'rejected_at' => 'datetime',
        'closed_at' => 'datetime',
        'status_corrected_at' => 'datetime',
    ];

    public function submitter()
    {
        return $this->belongsTo(Employees::class, 'submitted_by_employee_id');
    }

    public function targetBranch()
    {
        return $this->belongsTo(Branches::class, 'target_branch_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusCorrector()
    {
        return $this->belongsTo(User::class, 'status_corrected_by');
    }

    public function attachments()
    {
        return $this->hasMany(SuggestionAttachment::class);
    }

    public function rewards()
    {
        return $this->hasMany(SuggestionReward::class);
    }

    public function activeReward()
    {
        return $this->hasOne(SuggestionReward::class)
            ->whereIn('status', [SuggestionReward::STATUS_PENDING, SuggestionReward::STATUS_DELIVERED])
            ->latestOfMany();
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopeImplemented(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IMPLEMENTED);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::ALLOWED_TRANSITIONS[$this->status] ?? [], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function isEditableBySubmitter(): bool
    {
        return in_array($this->status, self::EDITABLE_BY_SUBMITTER_STATUSES, true);
    }

    public function hasEverBeenAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function requiresProposedSolution(): bool
    {
        return in_array($this->type, [self::TYPE_SUGGESTION, self::TYPE_IMPROVEMENT], true);
    }

    public function requiredFieldsForTransition(string $status): array
    {
        return self::TRANSITION_REQUIRED_FIELDS[$status] ?? [];
    }
}
