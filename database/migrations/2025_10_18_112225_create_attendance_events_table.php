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
        Schema::create('attendance_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_attendance')->constrained('attendances')->cascadeOnDelete();
            $t->enum('type', ['check_in','check_out','correction_in','correction_out','auto_close']);
            $t->timestamp('event_at');

            // provenance
            $t->enum('source', ['mobile','web','admin','device'])->default('mobile');
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();

            // geo (optional)
            $t->decimal('lat', 9, 6)->nullable();
            $t->decimal('lng', 9, 6)->nullable();
            $t->smallInteger('accuracy_m')->nullable();

            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['id_attendance','event_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
