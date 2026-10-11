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
        Schema::create('supplier_tt_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('goods_receipt_id')
                ->nullable()
                ->constrained('goods_receipts')
                ->restrictOnDelete();

            $table->foreignId('goods_receipt_invoice_id')
                ->nullable()
                ->constrained('goods_receipt_invoices')
                ->restrictOnDelete();

            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('received_date');
            $table->date('due_date');

            $table->decimal('gross_nominal', 15, 2)->default(0);
            $table->decimal('deduction_total', 15, 2)->default(0);
            $table->decimal('net_nominal', 15, 2)->default(0);

            $table->enum('status', [
                'draft',
                'verified',
                'disputed',
                'scheduled_payment',
                'paid',
                'cancelled',
            ])->default('draft');

            $table->text('verification_note')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('paid_at')->nullable();

            $table->foreignId('paid_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['supplier_id', 'invoice_number'], 'supplier_tt_supplier_invoice_unique');

            $table->index(['supplier_id', 'status'], 'supplier_tt_supplier_status_idx');
            $table->index(['goods_receipt_id', 'status'], 'supplier_tt_gr_status_idx');
            $table->index(['goods_receipt_invoice_id', 'status'], 'supplier_tt_gr_invoice_status_idx');
            $table->index(['due_date', 'status'], 'supplier_tt_due_status_idx');
            $table->index('invoice_number', 'supplier_tt_invoice_number_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_tt_invoices');
    }
};
