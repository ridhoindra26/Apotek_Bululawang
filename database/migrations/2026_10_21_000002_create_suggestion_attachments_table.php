<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestion_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained('suggestions')->cascadeOnDelete();
            $table->string('disk', 50)->default('local');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index(['suggestion_id', 'created_at'], 'suggestion_attachments_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestion_attachments');
    }
};
