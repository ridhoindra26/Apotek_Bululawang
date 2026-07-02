<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItemLine extends Model
{
    protected $fillable = [
        'payroll_item_id',
        'type',
        'name',
        'description',
        'amount',
        'source',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
