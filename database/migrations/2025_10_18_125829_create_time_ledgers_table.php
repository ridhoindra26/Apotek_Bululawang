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
        Schema::create('time_ledgers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_employee')->constrained('employees')->cascadeOnDelete();
            $t->date('work_date')->nullable();
            $t->foreignId('id_attendance')->nullable()->constrained('attendances')->cascadeOnDelete();
            $t->enum('type', [
                'penalty_add', 'penalty_reduce',
                'overtime_add', 'overtime_spend',
            ]);
            $t->integer('minutes');
            $t->string('source', 32)->default('system');
            $t->text('note')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_ledgers');
    }
};
