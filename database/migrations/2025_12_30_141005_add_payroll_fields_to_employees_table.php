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
        Schema::table('employees', function (Blueprint $table) {
            $table->bigInteger('base_salary')->default(0)->after('date_start');

            $table->string('bank_name')->nullable()->after('base_salary');
            $table->string('bank_account_number')->nullable()->after('bank_name'); // rekening
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');

            $table->string('payroll_email')->nullable()->after('bank_account_holder'); // if bank template needs EMAIL
            $table->boolean('payroll_active')->default(true)->after('payroll_email');

            $table->index(['payroll_active']);
            $table->index(['bank_account_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['payroll_active']);
            $table->dropIndex(['bank_account_number']);

            $table->dropColumn([
                'base_salary',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
                'payroll_email',
                'payroll_active',
            ]);
        });
    }
};
