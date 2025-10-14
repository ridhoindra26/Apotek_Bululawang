<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Basic identity fields
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();

            // Authentication
            $table->string('password');
            $table->rememberToken();

            // Role & Session management
            $table->string('role')->default('user'); // 'admin', 'manager', 'user', etc.
            $table->string('current_session_id')->nullable();

            // Optional audit fields
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
