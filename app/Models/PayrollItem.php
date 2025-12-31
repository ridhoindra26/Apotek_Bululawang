<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'id_employee',
        'rekening_snapshot',
        'email_snapshot',
        'base_salary_snapshot',
        'allowance_total',
        'deduction_total',
        'net_pay',
        'notes',
    ];

    protected $casts = [
        'base_salary_snapshot' => 'integer',
        'allowance_total' => 'integer',
        'deduction_total' => 'integer',
        'net_pay' => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'id_employee');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollItemLine::class);
    }
}
