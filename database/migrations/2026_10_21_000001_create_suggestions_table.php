<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submitted_by_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
            $table->string('submitter_name_snapshot');
            $table->string('submitter_branch_name_snapshot')->nullable();

            $table->foreignId('target_branch_id')
                ->nullable()
                ->constrained('branches')
                ->restrictOnDelete();

            $table->enum('type', ['suggestion', 'complaint', 'improvement']);
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('title');
            $table->text('previous_condition');
            $table->text('proposed_solution')->nullable();
            $table->text('expected_benefit')->nullable();
            $table->text('additional_notes')->nullable();

            $table->enum('status', [
                'submitted',
                'under_review',
                'accepted',
                'rejected',
                'planned',
                'in_progress',
                'implemented',
                'closed',
            ])->default('submitted');

            $table->enum('impact', ['low', 'medium', 'high'])->nullable();
            $table->enum('effort', ['low', 'medium', 'high'])->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->nullable();

            $table->text('rejection_reason')->nullable();
            $table->text('closure_reason')->nullable();
            $table->text('implementation_note')->nullable();

            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('implemented_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('last_status_correction_reason')->nullable();
            $table->timestamp('status_corrected_at')->nullable();
            $table->foreignId('status_corrected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['visibility', 'status', 'submitted_at'], 'suggestions_feed_idx');
            $table->index(['submitted_by_employee_id', 'status'], 'suggestions_submitter_status_idx');
            $table->index(['target_branch_id', 'status'], 'suggestions_target_branch_status_idx');
            $table->index(['type', 'status'], 'suggestions_type_status_idx');
            $table->index(['priority', 'status'], 'suggestions_priority_status_idx');
            $table->index('decided_at', 'suggestions_decided_at_idx');
            $table->index('implemented_at', 'suggestions_implemented_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
