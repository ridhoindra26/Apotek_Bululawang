<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->date('received_date')->nullable()->change();
            $table->foreignId('supplier_id')->nullable()->change();
        });

        Schema::table('supplier_tt_invoices', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->change();
            $table->string('invoice_number')->nullable()->change();
            $table->date('invoice_date')->nullable()->change();
            $table->date('received_date')->nullable()->change();
            $table->date('due_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->date('received_date')->nullable(false)->change();
            $table->foreignId('supplier_id')->nullable(false)->change();
        });

        Schema::table('supplier_tt_invoices', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable(false)->change();
            $table->string('invoice_number')->nullable(false)->change();
            $table->date('invoice_date')->nullable(false)->change();
            $table->date('received_date')->nullable(false)->change();
            $table->date('due_date')->nullable(false)->change();
        });
    }
};
