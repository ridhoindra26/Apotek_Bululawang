<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceiptInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'invoice_number',
        'invoice_date',
        'nominal',
        'evaluation_status',
        'evaluation_note',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function photos()
    {
        return $this->hasMany(GoodsReceiptInvoicePhoto::class)
            ->orderBy('page_number');
    }

    public function pages()
    {
        return $this->hasMany(GoodsReceiptInvoicePhoto::class)
            ->orderBy('page_number');
    }

    public function getEvaluationStatusLabelAttribute(): string
    {
        return match ($this->evaluation_status) {
            'unchecked' => 'Belum dicek',
            'matched' => 'Sesuai',
            'rejected' => 'Ditolak',
            'partially_rejected' => 'Ditolak sebagian',
            default => ucfirst((string) $this->evaluation_status),
        };
    }               
}
