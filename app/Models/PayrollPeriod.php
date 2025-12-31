<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'date_from',
        'date_to',
        'status',
        'created_by',
        'paid_at',
        'paid_note',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
