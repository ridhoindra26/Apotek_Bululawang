<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('employees', function (Blueprint $t) {
            $t->foreignId('default_pagi_shift_time_id')
              ->nullable()->after('id_branch')
              ->constrained('shift_times')->nullOnDelete();

            $t->foreignId('default_siang_shift_time_id')
              ->nullable()->after('default_pagi_shift_time_id')
              ->constrained('shift_times')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('employees', function (Blueprint $t) {
            $t->dropConstrainedForeignId('default_siang_shift_time_id');
            $t->dropConstrainedForeignId('default_pagi_shift_time_id');
        });
    }
};
