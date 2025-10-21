<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\CarbonInterval;

class Attendances extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'id_employee',
        'id_branch',
        'id_schedule',
        'work_date',
        'status',
        'check_in_at',
        'check_out_at',
        'work_minutes',
        'late_minutes',
        'penalty_minutes',
        'overtime_minutes',
        'overtime_applied_minutes',
        'early_leave_minutes',
        'early_checkin_minutes',
        'notes',
    ];

    protected $casts = [
        'work_date'          => 'date',
        'check_in_at'        => 'datetime',
        'check_out_at'       => 'datetime',
        'work_minutes'       => 'integer',
        'late_minutes'       => 'integer',
        'early_leave_minutes'=> 'integer',
        'early_checkin_minutes'=> 'integer',
        'penalty_minutes'    => 'integer',
        'overtime_minutes'   => 'integer',
        'overtime_applied_minutes' => 'integer',
    ];

    /** Relationships */
    public function employee() { return $this->belongsTo(Employees::class, 'id_employee'); }
    public function branch()   { return $this->belongsTo(Branches::class, 'id_branch'); }
    public function schedule() { return $this->belongsTo(Schedules::class, 'id_schedule'); }
    public function events()   { return $this->hasMany(AttendanceEvents::class, 'id_attendance'); }

    /** Scopes */
    public function scopeForEmployee($q, $employeeId) { return $q->where('id_employee', $employeeId); }
    public function scopeOnDate($q, $date)           { return $q->whereDate('work_date', $date); }
    public function scopeToday($q)                   { return $q->whereDate('work_date', now()->toDateString()); }

    /** Helpers */
    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->check_in_at) && !is_null($this->check_out_at);
    }

    public function getWorkDurationAttribute(): ?CarbonInterval
    {
        return is_null($this->work_seconds) ? null : CarbonInterval::seconds($this->work_seconds);
    }
}
