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
        Schema::create('payroll_transfer_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('delimiter', 5)->default(','); // "," or ";" etc.

            // Template columns (order + exact header + mapping)
            $table->json('columns_json');

            // Optional knobs for bank import quirks
            $table->enum('encoding', ['utf8', 'utf8_bom'])->default('utf8');
            $table->boolean('include_header')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_transfer_templates');
    }
};
