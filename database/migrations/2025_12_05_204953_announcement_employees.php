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
        Schema::create('announcement_employee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('id_employee');
            $table->timestamps();

            $table->foreign('announcement_id')
                ->references('id')->on('announcements')
                ->cascadeOnDelete();

            $table->foreign('id_employee')
                ->references('id')->on('employees')
                ->cascadeOnDelete();

            $table->unique(['announcement_id', 'id_employee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_employee');
    }
};
