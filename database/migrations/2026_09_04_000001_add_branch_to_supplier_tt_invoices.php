<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('supplier_tt_invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('supplier_id')->constrained('branches')->restrictOnDelete();
            $table->index(['branch_id', 'status']);
        });

        DB::table('supplier_tt_invoices')->where('status', 'disputed')->update([
            'status' => 'draft',
            'verified_at' => null,
            'verified_by' => null,
        ]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE supplier_tt_invoices MODIFY status ENUM('draft','verified','scheduled_payment','paid','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE supplier_tt_invoices MODIFY status ENUM('draft','verified','disputed','scheduled_payment','paid','cancelled') NOT NULL DEFAULT 'draft'");
        }
        Schema::table('supplier_tt_invoices', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
    }
};
