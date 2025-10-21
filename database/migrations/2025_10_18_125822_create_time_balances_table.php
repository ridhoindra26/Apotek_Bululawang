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
        Schema::create('time_balances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_employee')->constrained('employees')->cascadeOnDelete();
            $t->integer('debt_minutes')->default(0);   // penalty owed
            $t->integer('credit_minutes')->default(0); // overtime bank
            $t->timestamps();
            $t->unique('id_employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_balances');
    }
};
