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
        Schema::create('goods_receipt_invoice_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('goods_receipt_invoice_id')
                ->constrained('goods_receipt_invoices')
                ->cascadeOnDelete();

            $table->string('path');

            // Untuk urutan halaman faktur
            $table->unsignedInteger('page_number')->nullable();

            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime_type')->nullable();

            $table->timestamps();

            $table->index(
                ['goods_receipt_invoice_id', 'page_number'],
                'gr_invoice_photo_page_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_invoice_photos');
    }
};
