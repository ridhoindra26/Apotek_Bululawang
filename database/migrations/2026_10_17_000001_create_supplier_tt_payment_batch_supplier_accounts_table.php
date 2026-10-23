<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_tt_payment_batch_supplier_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_batch_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->timestamps();

            $table->unique(
                ['payment_batch_id', 'supplier_id'],
                'supplier_tt_batch_supplier_account_unique'
            );
            $table->foreign('payment_batch_id', 'supplier_tt_batch_account_batch_fk')
                ->references('id')
                ->on('supplier_tt_payment_batches')
                ->cascadeOnDelete();
            $table->foreign('supplier_id', 'supplier_tt_batch_account_supplier_fk')
                ->references('id')
                ->on('suppliers')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_tt_payment_batch_supplier_accounts');
    }
};
