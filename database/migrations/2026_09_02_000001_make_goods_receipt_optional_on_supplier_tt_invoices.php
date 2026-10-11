<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_tt_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
            $table->unsignedBigInteger('goods_receipt_invoice_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_tt_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable(false)->change();
            $table->unsignedBigInteger('goods_receipt_invoice_id')->nullable(false)->change();
        });
    }
};
