<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_times', function (Blueprint $t) {
            $t->id();
            $t->enum('group', ['Pagi', 'Siang']); // aligns with your schedules.shift
            $t->string('code')->nullable();       // optional label, e.g. "P1", "S3"
            $t->time('start_time');               // e.g. 06:50
            $t->time('end_time');                 // e.g. 14:50
            $t->boolean('spans_midnight')->default(false); // just in case
            // policy knobs (all in minutes)
            $t->smallInteger('tolerance_late_minutes')->default(0);
            $t->smallInteger('tolerance_early_minutes')->default(0);
            $t->smallInteger('break_minutes')->default(0);
            $t->timestamps();
        });

        Schema::table('schedules', function (Blueprint $t) {
            $t->foreignId('id_shift_time')
              ->nullable()
              ->after('shift')
              ->constrained('shift_times')
              ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $t) {
            $t->dropConstrainedForeignId('id_shift_time');
        });
        Schema::dropIfExists('shift_times');
    }
};
