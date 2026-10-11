<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_tt_payment_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('total_nominal', 15, 2)->default(0);
            $table->enum('status', ['draft', 'locked', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['branch_id', 'status', 'payment_date'], 'supplier_tt_batch_branch_status_date_idx');
        });

        Schema::create('supplier_tt_payment_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_batch_id')->constrained('supplier_tt_payment_batches')->cascadeOnDelete();
            $table->foreignId('supplier_tt_invoice_id')->unique()->constrained('supplier_tt_invoices')->restrictOnDelete();
            $table->decimal('nominal_snapshot', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_tt_payment_batch_items');
        Schema::dropIfExists('supplier_tt_payment_batches');
    }
};
