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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_branch')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('id_employee')->constrained('employees')->cascadeOnDelete();
            $table->date('date');                      // Y-m-d (month/year derive from here)
            $table->enum('shift', ['Pagi','Siang']);   // libur tetap "Pagi"
            $table->boolean('is_vacation')->default(false);
            $table->timestamps();

            // Seorang karyawan hanya boleh punya satu entri per tanggal
            $table->unique(['id_employee', 'date']);

            // Query cepat
            $table->index(['date']);                    // filter per bulan
            $table->index(['id_branch','date']);        // per cabang per hari/bulan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void { Schema::dropIfExists('schedules'); }
};
