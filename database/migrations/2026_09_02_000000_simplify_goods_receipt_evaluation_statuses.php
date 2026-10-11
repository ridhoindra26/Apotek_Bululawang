<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY evaluation_status ENUM('unchecked','matched','not_matched','partially_matched','ed_near_rejected','rejected','partially_rejected') NOT NULL DEFAULT 'unchecked'");
            DB::statement("ALTER TABLE goods_receipt_invoices MODIFY evaluation_status ENUM('unchecked','matched','not_matched','ed_near_rejected','rejected','partially_rejected') NOT NULL DEFAULT 'unchecked'");
        } else {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'not_matched', 'partially_matched', 'ed_near_rejected', 'rejected', 'partially_rejected'])
                    ->default('unchecked')
                    ->change();
            });
            Schema::table('goods_receipt_invoices', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'not_matched', 'ed_near_rejected', 'rejected', 'partially_rejected'])
                    ->default('unchecked')
                    ->change();
            });
        }

        DB::table('goods_receipt_invoices')
            ->whereIn('evaluation_status', ['not_matched', 'ed_near_rejected'])
            ->update(['evaluation_status' => 'rejected']);

        DB::table('goods_receipts')
            ->whereIn('evaluation_status', ['not_matched', 'ed_near_rejected'])
            ->update(['evaluation_status' => 'rejected']);

        DB::table('goods_receipts')
            ->where('evaluation_status', 'partially_matched')
            ->update(['evaluation_status' => 'partially_rejected']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY evaluation_status ENUM('unchecked','matched','rejected','partially_rejected') NOT NULL DEFAULT 'unchecked'");
            DB::statement("ALTER TABLE goods_receipt_invoices MODIFY evaluation_status ENUM('unchecked','matched','rejected','partially_rejected') NOT NULL DEFAULT 'unchecked'");
        } else {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'rejected', 'partially_rejected'])
                    ->default('unchecked')
                    ->change();
            });
            Schema::table('goods_receipt_invoices', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'rejected', 'partially_rejected'])
                    ->default('unchecked')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY evaluation_status ENUM('unchecked','matched','not_matched','partially_matched','ed_near_rejected','rejected','partially_rejected') NOT NULL DEFAULT 'unchecked'");
            DB::statement("ALTER TABLE goods_receipt_invoices MODIFY evaluation_status ENUM('unchecked','matched','not_matched','ed_near_rejected','rejected','partially_rejected') NOT NULL DEFAULT 'unchecked'");
        } else {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'not_matched', 'partially_matched', 'ed_near_rejected', 'rejected', 'partially_rejected'])
                    ->default('unchecked')
                    ->change();
            });
            Schema::table('goods_receipt_invoices', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'not_matched', 'ed_near_rejected', 'rejected', 'partially_rejected'])
                    ->default('unchecked')
                    ->change();
            });
        }

        DB::table('goods_receipts')
            ->where('evaluation_status', 'partially_rejected')
            ->update(['evaluation_status' => 'partially_matched']);

        DB::table('goods_receipt_invoices')
            ->where('evaluation_status', 'partially_rejected')
            ->update(['evaluation_status' => 'not_matched']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY evaluation_status ENUM('unchecked','matched','not_matched','partially_matched','ed_near_rejected','rejected') NOT NULL DEFAULT 'unchecked'");
            DB::statement("ALTER TABLE goods_receipt_invoices MODIFY evaluation_status ENUM('unchecked','matched','not_matched','ed_near_rejected','rejected') NOT NULL DEFAULT 'unchecked'");
        } else {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'not_matched', 'partially_matched', 'ed_near_rejected', 'rejected'])
                    ->default('unchecked')
                    ->change();
            });
            Schema::table('goods_receipt_invoices', function (Blueprint $table) {
                $table->enum('evaluation_status', ['unchecked', 'matched', 'not_matched', 'ed_near_rejected', 'rejected'])
                    ->default('unchecked')
                    ->change();
            });
        }
    }
};
