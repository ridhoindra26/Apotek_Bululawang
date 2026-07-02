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
        Schema::create('cashier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('employees'); // atau employees
            $table->enum('type', [
                'closing_cash',   // kertas tutup kasir
                'deposit_slip',   // bukti setoran
                'blood_check',    // bukti cek darah
                'petty_cash',     // foto kas kecil
            ]);
            $table->date('date');
            $table->enum('shift', ['Pagi', 'Siang']);
            $table->text('description')->nullable();
            $table->string('photo_path'); // simpan path foto

            $table->enum('status', ['pending', 'confirmed', 'rejected'])
                  ->default('pending');

            $table->string('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_documents');
    }
};
