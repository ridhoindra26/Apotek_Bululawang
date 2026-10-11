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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();

            $table->date('received_date');

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // SP masih offline, jadi cukup simpan nomor referensi
            $table->string('sp_reference_number')->nullable();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->string('sender_name')->nullable();

            $table->text('notes')->nullable();

            $table->decimal('total_nominal', 15, 2)->default(0);

            $table->enum('evaluation_status', [
                'unchecked',
                'matched',
                'not_matched',
                'partially_matched',
                'ed_near_rejected',
                'rejected',
            ])->default('unchecked');

            $table->text('evaluation_note')->nullable();

            $table->enum('status', [
                'draft',
                'checking',
                'waiting_approval',
                'approved',
                'rejected',
                'cancelled',
            ])->default('draft');

            $table->timestamp('checked_at')->nullable();

            $table->foreignId('checked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // $table->timestamp('approved_at')->nullable();

            // $table->foreignId('approved_by')
            //     ->nullable()
            //     ->constrained('users')
            //     ->nullOnDelete();

            $table->timestamps();

            $table->index(['received_date', 'supplier_id']);
            $table->index(['status', 'evaluation_status']);
            $table->index('sp_reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
