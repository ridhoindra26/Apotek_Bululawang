<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierTtPaymentBatchSupplierAccount extends Model
{
    protected $fillable = [
        'payment_batch_id',
        'supplier_id',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
    ];

    public function batch()
    {
        return $this->belongsTo(SupplierTtPaymentBatch::class, 'payment_batch_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
