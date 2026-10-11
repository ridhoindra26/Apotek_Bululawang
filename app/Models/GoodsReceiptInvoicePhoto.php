<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Storage;

class GoodsReceiptInvoicePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_invoice_id',
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
            GoodsReceiptInvoice::class,
            'goods_receipt_invoice_id'
        );
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getPageLabelAttribute(): string
    {
        return $this->page_number
            ? 'Halaman ' . $this->page_number
            : 'Halaman';
    }
}
