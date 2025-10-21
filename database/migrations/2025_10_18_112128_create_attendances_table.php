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
        Schema::create('attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_employee')->constrained('employees')->cascadeOnDelete();
            $t->date('work_date');                                   // e.g. 2025-10-18
            $t->enum('status', ['not_checked_in','in_progress','completed','missed','on_leave'])
            ->default('not_checked_in');

            $t->foreignId('id_branch')->nullable()->constrained('branches');
            $t->foreignId('id_schedule')->nullable()->constrained('schedules');

            // quick fields for reporting
            $t->timestamp('check_in_at')->nullable();
            $t->timestamp('check_out_at')->nullable();
            $t->integer('work_seconds')->nullable();                 // cached duration
            $t->integer('late_seconds')->nullable();
            $t->integer('early_leave_seconds')->nullable();

            $t->text('notes')->nullable();
            $t->timestamps();

            $t->unique(['id_employee','work_date']);                 // 1 row per day per employee
            $t->index(['work_date','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
