<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestion_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained('suggestions')->cascadeOnDelete();
            $table->enum('type', ['money', 'gift']);
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('gift_description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['suggestion_id', 'status'], 'suggestion_rewards_parent_status_idx');
            $table->index(['status', 'created_at'], 'suggestion_rewards_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestion_rewards');
    }
};
