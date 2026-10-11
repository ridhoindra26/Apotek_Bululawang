<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupplierTtInvoicePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_tt_invoice_id',
        'path',
        'page_number',
        'original_name',
        'size',
        'mime_type',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'size' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(
            SupplierTtInvoice::class,
            'supplier_tt_invoice_id'
        );
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getPageLabelAttribute(): string
    {
        return 'Halaman ' . ($this->page_number ?: 1);
    }
}