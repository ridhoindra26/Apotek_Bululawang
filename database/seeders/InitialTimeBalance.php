<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        $employees = Employees::all();

        if ($employees->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada data karyawan di tabel employees.');
            return;
        }

        foreach ($employees as $employee) {
            // --- 1️⃣ Buat atau update saldo awal di TimeBalances ---
            $balance = TimeBalances::updateOrCreate(
                ['id_employee' => $employee->id],
                [
                    'debt_minutes'   => 0,
                    'credit_minutes' => 0,
                ]
            );

            // --- 2️⃣ Buat ledger awal hanya jika belum ada ---
            $existingLedger = TimeLedgers::where('id_employee', $employee->id)
                ->where('source', 'initial_balance')
                ->first();

            if (!$existingLedger) {
                TimeLedgers::create([
                    'id_employee'   => $employee->id,
                    'work_date'     => Carbon::now()->startOfDay(),
                    'id_attendance' => null,
                    'type'          => 'initial_balance', // bisa diubah ke 'debt' jika sistem kamu terbalik
                    'minutes'       => 0,
                    'source'        => 'system',
                    'note'          => 'Initial Balance ',
                ]);
            }
        }

        $this->command->info('✅ Time balance dan ledger awal berhasil dibuat untuk semua karyawan.');
    }
}
