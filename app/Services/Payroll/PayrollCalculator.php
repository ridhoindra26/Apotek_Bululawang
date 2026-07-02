<?php

namespace App\Services\Payroll;

use App\Models\PayrollItem;

class PayrollCalculator
{
    public function recalculate(PayrollItem $item): PayrollItem
    {
        $allowance = (int) $item->lines()->where('type', 'allowance')->sum('amount');
        $deduction = (int) $item->lines()->where('type', 'deduction')->sum('amount');
        $base = (int) $item->base_salary_snapshot;

        $item->allowance_total = $allowance;
        $item->deduction_total = $deduction;
        $item->net_pay = $base + $allowance - $deduction;

        $item->save();

        return $item;
    }
}