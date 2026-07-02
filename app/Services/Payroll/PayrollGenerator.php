<?php

namespace App\Services\Payroll;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\Employees;
use Illuminate\Support\Facades\DB;

class PayrollGenerator
{
    public function generateForPeriod(PayrollPeriod $period, array $employeeIds): array
    {
        $created = 0;
        $skipped = 0;

        $employees = Employees::query()
            ->whereIn('id', $employeeIds)
            ->get();

        DB::transaction(function () use ($period, $employees, &$created, &$skipped) {
            foreach ($employees as $emp) {

                $exists = PayrollItem::query()
                    ->where('payroll_period_id', $period->id)
                    ->where('id_employee', $emp->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                PayrollItem::create([
                    'payroll_period_id' => $period->id,
                    'id_employee' => $emp->id,

                    // snapshots (adjust to your columns)
                    'base_salary_snapshot' => (int) ($emp->base_salary ?? 0),
                    'rekening_snapshot' => (string) ($emp->bank_account_number ?? ''),
                    'email_snapshot' => (string) ($emp->payroll_email ?? ''),

                    'allowance_total' => 0,
                    'deduction_total' => 0,
                    'net_pay' => (int) ($emp->base_salary ?? 0),
                ]);

                $created++;
            }
        });

        return compact('created', 'skipped');
    }
}