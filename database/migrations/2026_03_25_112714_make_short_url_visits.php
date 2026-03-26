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
        Schema::create('short_url_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('source_app')->nullable(); // public-shortener
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_url_visits');
    }
};
