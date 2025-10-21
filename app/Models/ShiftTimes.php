<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftTimes extends Model
{
    protected $table = 'shift_times';
    protected $fillable = [
        'group','code','start_time','end_time','spans_midnight',
        'tolerance_late_minutes','tolerance_early_minutes','break_minutes'
    ];

    protected $casts = [
        'spans_midnight' => 'boolean',
    ];

    public function schedules() { return $this->hasMany(Schedules::class, 'id_shift_time'); }
}
