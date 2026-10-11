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
        Schema::create('supplier_tt_invoice_deductions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_tt_invoice_id')
                ->constrained('supplier_tt_invoices')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Deduction Type
            |--------------------------------------------------------------------------
            */
            $table->enum('type', [
                'return',
                'damaged_goods',
                'price_correction',
                'discount',
                'promo',
                'rounding',
                'other',
            ])->default('other');

            $table->decimal('amount', 15, 2)->default(0);

            $table->string('reference_number')->nullable();
            $table->date('reference_date')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(
                ['supplier_tt_invoice_id', 'type'],
                'supplier_tt_deduction_invoice_type_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_tt_invoice_deductions');
    }
};
