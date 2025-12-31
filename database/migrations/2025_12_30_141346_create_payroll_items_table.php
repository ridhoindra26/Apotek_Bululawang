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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_period_id')
                ->constrained('payroll_periods')
                ->cascadeOnDelete();

            $table->foreignId('id_employee')
                ->constrained('employees')
                ->restrictOnDelete();

            // snapshots for export stability
            $table->string('rekening_snapshot');
            $table->string('email_snapshot')->nullable();
            $table->bigInteger('base_salary_snapshot')->default(0);

            $table->bigInteger('allowance_total')->default(0);
            $table->bigInteger('deduction_total')->default(0);
            $table->bigInteger('net_pay')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['payroll_period_id', 'id_employee']);
            $table->index(['payroll_period_id']);
            $table->index(['id_employee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
