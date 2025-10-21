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
        Schema::table('attendances', function (Blueprint $t) {
            $t->integer('penalty_minutes')->default(0)->after('late_seconds');   // changed name for clarity
            $t->integer('overtime_minutes')->default(0)->after('penalty_minutes');
            $t->integer('overtime_applied_minutes')->default(0)->after('overtime_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
