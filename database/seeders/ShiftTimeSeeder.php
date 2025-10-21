<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            // Pagi
            ['group' => 'Pagi', 'code'=>'P1', 'start_time'=>'06:50', 'end_time'=>'14:50', 'tolerance_late_minutes'=>5, 'tolerance_early_minutes'=>0, 'break_minutes'=>0],
            ['group' => 'Pagi', 'code'=>'P2', 'start_time'=>'06:50', 'end_time'=>'13:50', 'tolerance_late_minutes'=>5, 'tolerance_early_minutes'=>0, 'break_minutes'=>0],
            // Siang
            ['group' => 'Siang','code'=>'S1', 'start_time'=>'13:00', 'end_time'=>'21:00', 'tolerance_late_minutes'=>5, 'tolerance_early_minutes'=>0, 'break_minutes'=>0],
            ['group' => 'Siang','code'=>'S2', 'start_time'=>'14:00', 'end_time'=>'21:00', 'tolerance_late_minutes'=>5, 'tolerance_early_minutes'=>0, 'break_minutes'=>0],
            ['group' => 'Siang','code'=>'S3', 'start_time'=>'09:00', 'end_time'=>'17:00', 'tolerance_late_minutes'=>5, 'tolerance_early_minutes'=>0, 'break_minutes'=>0],
        ];

        foreach ($rows as $r) {
            DB::table('shift_times')->updateOrInsert(
                ['group'=>$r['group'], 'code'=>$r['code'], 'start_time'=>$r['start_time'], 'end_time'=>$r['end_time']],
                $r + ['created_at'=>now(), 'updated_at'=>now()]
            );
        }
    }
}
