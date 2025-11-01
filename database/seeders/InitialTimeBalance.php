<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use App\Models\Employees;
use App\Models\TimeBalances;
use App\Models\TimeLedgers;
use Carbon\Carbon;

class InitialTimeBalance extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();

        $employees = Employees::all();
        if ($employees->isEmpty()) {
            $this->command->warn('⚠️ No employees found.');
            return;
        }

        DB::transaction(function () use ($employees, $today) {
            foreach ($employees as $employee) {

                // 1️⃣ Always reset balance to 0 — overwrite any previous value
                TimeBalances::updateOrCreate(
                    ['id_employee' => $employee->id],
                    [
                        'debt_minutes'   => 0,
                        'credit_minutes' => 0,
                    ]
                );

                // 2️⃣ Create or reset the initial ledger entry
                TimeLedgers::updateOrCreate(
                    [
                        'id_employee' => $employee->id,
                        'source'      => 'system',
                    ],
                    [
                        'work_date'     => $today->startOfDay(),
                        'id_attendance' => null,
                        'type'          => 'initial_balance', // must match enum ('credit' or 'debt')
                        'minutes'       => 0,
                        'note'          => 'Initial balance reset to 0',
                    ]
                );
            }
        });

        $this->command->info('✅ All time balances reset to 0 and initial ledgers ensured for each employee.');
    }
}
