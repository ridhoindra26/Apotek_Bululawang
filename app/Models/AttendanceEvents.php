<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceEvents extends Model
{
    use HasFactory;

    protected $table = 'attendance_events';

    protected $fillable = [
        'id_attendance',
        'type',        // check_in, check_out, correction_in, correction_out, auto_close
        'event_at',
        'source',      // mobile, web, admin, device
        'ip_address',
        'user_agent',
        'lat',
        'lng',
        'accuracy_m',
        'notes',
    ];

    protected $casts = [
        'event_at'   => 'datetime',
        'lat'        => 'decimal:6',
        'lng'        => 'decimal:6',
        'accuracy_m' => 'integer',
    ];

    /** Relationships */
    public function attendance() { return $this->belongsTo(Attendances::class, 'id_attendance'); }
    public function photos()     { return $this->hasMany(AttendancePhotos::class, 'id_attendance_event'); }

    /** Scopes */
    public function scopeType($q, string $type) { return $q->where('type', $type); }
    public function scopeBetween($q, $from, $to) { return $q->whereBetween('event_at', [$from, $to]); }
}
