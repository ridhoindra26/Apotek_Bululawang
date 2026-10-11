<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierTtInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'goods_receipt_id',
        'goods_receipt_invoice_id',
        'invoice_number',
        'invoice_date',
        'received_date',
        'due_date',
        'gross_nominal',
        'deduction_total',
        'net_nominal',
        'status',
        'verification_note',
        'notes',
        'verified_at',
        'verified_by',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'received_date' => 'date',
        'due_date' => 'date',
        'gross_nominal' => 'decimal:2',
        'deduction_total' => 'decimal:2',
        'net_nominal' => 'decimal:2',
        'verified_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branches::class, 'branch_id');
    }

    public function paymentBatch()
    {
        return $this->belongsToMany(SupplierTtPaymentBatch::class, 'supplier_tt_payment_batch_items', 'supplier_tt_invoice_id', 'payment_batch_id')
            ->withPivot('nominal_snapshot')->withTimestamps();
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function goodsReceiptInvoice()
    {
        return $this->belongsTo(GoodsReceiptInvoice::class, 'goods_receipt_invoice_id');
    }

    public function photos()
    {
        return $this->hasMany(SupplierTtInvoicePhoto::class)
            ->orderBy('page_number');
    }

    public function deductions()
    {
        return $this->hasMany(SupplierTtInvoiceDeduction::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            'draft',
            'verified',
            'scheduled_payment',
            'paid',
        ]);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', [
            'paid',
            'cancelled',
        ]);
    }

    public function scopeDueToday($query)
    {
        return $query->unpaid()
            ->whereDate('due_date', today());
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->unpaid()
            ->whereBetween('due_date', [
                today()->addDay(),
                today()->addDays($days),
            ]);
    }

    public function scopeOverdue($query)
    {
        return $query->unpaid()
            ->whereDate('due_date', '<', today());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'verified' => 'Terverifikasi',
            'scheduled_payment' => 'Dijadwalkan Bayar',
            'paid' => 'Dibayar',
            'cancelled' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function getDueStatusAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'paid';
        }

        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if (!$this->due_date) {
            return 'unknown';
        }

        if ($this->due_date->isPast() && !$this->due_date->isToday()) {
            return 'overdue';
        }

        if ($this->due_date->isToday()) {
            return 'due_today';
        }

        if ($this->due_date->lte(today()->addDays(7))) {
            return 'due_soon';
        }

        return 'normal';
    }

    public function getDueStatusLabelAttribute(): string
    {
        return match ($this->due_status) {
            'paid' => 'Sudah Dibayar',
            'cancelled' => 'Dibatalkan',
            'overdue' => 'Lewat Jatuh Tempo',
            'due_today' => 'Jatuh Tempo Hari Ini',
            'due_soon' => 'Segera Jatuh Tempo',
            'normal' => 'Normal',
            default => 'Tidak Diketahui',
        };
    }

    public function recalculateNominal(): void
    {
        $deductionTotal = $this->deductions()->sum('amount');

        $grossNominal = (float) $this->gross_nominal;
        $netNominal = max(0, $grossNominal - (float) $deductionTotal);

        $this->update([
            'deduction_total' => $deductionTotal,
            'net_nominal' => $netNominal,
        ]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'verified']);
    }

    public function isDeletable(): bool
    {
        return !in_array($this->status, [
            'paid',
        ]);
    }
}
