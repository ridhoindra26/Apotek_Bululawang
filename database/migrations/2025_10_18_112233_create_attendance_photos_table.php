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
        Schema::create('attendance_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_attendance_event')->constrained('attendance_events')->cascadeOnDelete();
            $t->string('disk');                 // e.g. 'public'
            $t->string('path');                 // e.g. 'attendance/2025/10/18/abc.jpg'
            $t->string('mime', 64);
            $t->integer('size_kb');
            $t->integer('width')->nullable();
            $t->integer('height')->nullable();
            $t->string('hash', 128)->nullable();
            $t->timestamps();

            $t->index('id_attendance_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_photos');
    }
};
