<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'date_from',
        'date_to',
        'created_by',
    ];

    protected $dates = [
        'date_from',
        'date_to',
    ];

    // ───────── Relations ─────────

    public function employees()
    {
        // match pivot table + FK names
        return $this->belongsToMany(Employees::class, 'announcement_employee', 'announcement_id', 'id_employee')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ───────── Scopes & Accessors ─────────

    public function scopeActive($q)
    {
        $today = now()->toDateString();

        return $q->whereDate('date_from', '<=', $today)
                 ->where(function ($sub) use ($today) {
                     $sub->whereNull('date_to')
                         ->orWhereDate('date_to', '>=', $today);
                 });
    }

    public function getIsActiveAttribute(): bool
    {
        $today = Carbon::today();

        $fromOk = $this->date_from ? $today->greaterThanOrEqualTo($this->date_from) : true;
        $toOk   = $this->date_to ? $today->lessThanOrEqualTo($this->date_to) : true;

        return $fromOk && $toOk;
    }
}
