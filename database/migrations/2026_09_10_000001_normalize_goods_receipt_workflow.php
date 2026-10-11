<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('goods_receipts')->where('status', 'checking')->update(['status' => 'draft']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY status ENUM('draft','waiting_approval','approved','rejected','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_receipts MODIFY status ENUM('draft','checking','waiting_approval','approved','rejected','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
