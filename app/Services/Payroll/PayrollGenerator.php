<?php

namespace App\Services\Payroll;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\Employees;
use Illuminate\Support\Facades\DB;

class PayrollGenerator
{
    public function generateForPeriod(PayrollPeriod $period): int
    {
        if ($period->status !== 'draft') {
            throw new \RuntimeException('Payroll period must be draft to generate items.');
        }

        return DB::transaction(function () use ($period) {

            $employees = Employees::query()
                ->where('payroll_active', true)
                ->get();

            $created = 0;

            foreach ($employees as $employee) {
                // Ensure rekening exists for transfer
                if (empty($employee->bank_account_number)) {
                    continue;
                }

                // Avoid overwriting if already generated
                $exists = PayrollItem::query()
                    ->where('payroll_period_id', $period->id)
                    ->where('id_employee', $employee->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $base = (int) ($employee->base_salary ?? 0);

                PayrollItem::create([
                    'payroll_period_id' => $period->id,
                    'id_employee' => $employee->id,

                    'rekening_snapshot' => $employee->bank_account_number,
                    'email_snapshot' => $employee->payroll_email,
                    'base_salary_snapshot' => $base,

                    'allowance_total' => 0,
                    'deduction_total' => 0,
                    'net_pay' => $base,
                ]);

                $created++;
            }

            return $created;
        });
    }
}