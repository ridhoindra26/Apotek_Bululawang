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
        Schema::create('payroll_item_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_item_id')
                ->constrained('payroll_items')
                ->cascadeOnDelete();

            $table->enum('type', ['allowance', 'deduction']);
            $table->string('name');                 // short label
            $table->text('description')->nullable(); // detailed explanation
            $table->bigInteger('amount');           // positive integer only

            $table->enum('source', ['manual', 'time_balance', 'import'])
                ->default('manual');

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['payroll_item_id', 'type']);
            $table->index(['source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_item_lines');
    }
};
