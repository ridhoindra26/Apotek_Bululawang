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
        Schema::create('supplier_tt_invoice_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_tt_invoice_id')
                ->constrained('supplier_tt_invoices')
                ->cascadeOnDelete();

            $table->string('path');
            $table->unsignedInteger('page_number')->default(1);

            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime_type')->nullable();

            $table->timestamps();

            $table->index(
                ['supplier_tt_invoice_id', 'page_number'],
                'supplier_tt_photo_invoice_page_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_tt_invoice_photos');
    }
};
