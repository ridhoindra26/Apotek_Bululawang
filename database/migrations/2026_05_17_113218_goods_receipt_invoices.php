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
        Schema::create('goods_receipt_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('goods_receipt_id')
                ->constrained('goods_receipts')
                ->cascadeOnDelete();

            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            $table->decimal('nominal', 15, 2)->default(0);

            $table->enum('evaluation_status', [
                'unchecked',
                'matched',
                'not_matched',
                'ed_near_rejected',
                'rejected',
            ])->default('unchecked');

            $table->text('evaluation_note')->nullable();

            $table->timestamps();

            $table->index('invoice_number');
            $table->index(['goods_receipt_id', 'evaluation_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_invoices');
    }
};
