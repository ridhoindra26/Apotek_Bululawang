<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->timestamp('planned_at')->nullable()->after('accepted_at');
            $table->timestamp('in_progress_at')->nullable()->after('planned_at');
            $table->timestamp('rejected_at')->nullable()->after('implemented_at');
            $table->timestamp('closed_at')->nullable()->after('rejected_at');
            $table->string('status_before_correction', 30)->nullable()->after('last_status_correction_reason');
            $table->string('status_after_correction', 30)->nullable()->after('status_before_correction');
        });
    }

    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropColumn([
                'planned_at', 'in_progress_at', 'rejected_at', 'closed_at',
                'status_before_correction', 'status_after_correction',
            ]);
        });
    }
};
