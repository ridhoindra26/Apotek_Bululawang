<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierTtInvoiceDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_tt_invoice_id',
        'type',
        'amount',
        'reference_number',
        'reference_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reference_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(
            SupplierTtInvoice::class,
            'supplier_tt_invoice_id'
        );
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'return' => 'Retur',
            'damaged_goods' => 'Barang Rusak',
            'price_correction' => 'Koreksi Harga',
            'discount' => 'Diskon',
            'promo' => 'Promo',
            'rounding' => 'Pembulatan',
            'other' => 'Lainnya',
            default => ucfirst((string) $this->type),
        };
    }
}