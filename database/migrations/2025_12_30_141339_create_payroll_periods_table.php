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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique(); // e.g. 2025-10
            $table->string('name');           // e.g. Gaji Oktober 2025

            $table->date('date_from');
            $table->date('date_to');

            $table->enum('status', ['draft', 'locked', 'paid', 'void'])->default('draft');

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'date_from', 'date_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
