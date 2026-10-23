<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->index(['submitted_at', 'target_branch_id', 'type'], 'suggestions_analytics_filter_idx');
            $table->index('accepted_at', 'suggestions_accepted_at_idx');
            $table->index(['status', 'review_started_at'], 'suggestions_attention_review_idx');
        });
        Schema::table('suggestion_rewards', function (Blueprint $table) {
            $table->index(['status', 'type', 'delivered_at'], 'suggestion_rewards_delivery_analytics_idx');
        });
    }

    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropIndex('suggestions_analytics_filter_idx');
            $table->dropIndex('suggestions_accepted_at_idx');
            $table->dropIndex('suggestions_attention_review_idx');
        });
        Schema::table('suggestion_rewards', function (Blueprint $table) {
            $table->dropIndex('suggestion_rewards_delivery_analytics_idx');
        });
    }
};
