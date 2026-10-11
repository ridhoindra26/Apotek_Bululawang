<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_date',
        'supplier_id',
        'branch_id',
        'sp_reference_number',
        'received_by',
        'sender_name',
        'notes',
        'total_nominal',
        'evaluation_status',
        'evaluation_note',
        'status',
        'checked_at',
        'checked_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'total_nominal' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branches::class, 'branch_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Employees::class, 'received_by');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function invoices()
    {
        return $this->hasMany(GoodsReceiptInvoice::class);
    }

    public function signatures()
    {
        return $this->hasOne(GoodsReceiptSignature::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'checking' => 'Checking',
            'waiting_approval' => 'Menunggu Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
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

    public function supplierTtInvoices()
    {
        return $this->hasMany(SupplierTtInvoice::class, 'goods_receipt_id');
    }

    public function getActiveTtTotalAttribute(): float
    {
        if ($this->relationLoaded('supplierTtInvoices')) {
            return (float) $this->supplierTtInvoices
                ->whereIn('status', [
                    'draft',
                    'verified',
                    'disputed',
                    'scheduled_payment',
                    'paid',
                ])
                ->sum('net_nominal');
        }

        return (float) $this->supplierTtInvoices()
            ->active()
            ->sum('net_nominal');
    }

    public function getTtRemainingNominalAttribute(): float
    {
        return max(0, (float) $this->total_nominal - $this->active_tt_total);
    }

    public function getTtCountAttribute(): int
    {
        if ($this->relationLoaded('supplierTtInvoices')) {
            return $this->supplierTtInvoices
                ->where('status', '!=', 'cancelled')
                ->count();
        }

        return $this->supplierTtInvoices()
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    public function getTtCountLabelAttribute(): string
    {
        return match (true) {
            $this->tt_count === 0 => 'Belum Ada Faktur TT',
            $this->tt_count === 1 => '1 Faktur TT',
            $this->tt_count > 1 => 'Multi Faktur TT',
            default => '-',
        };
    }

    public function getTtAmountStatusAttribute(): string
    {
        $activeTtTotal = $this->active_tt_total;
        $totalNominal = (float) $this->total_nominal;

        if ($activeTtTotal <= 0) {
            return 'no_tt';
        }

        if ($activeTtTotal < $totalNominal) {
            return 'partial';
        }

        if ($activeTtTotal == $totalNominal) {
            return 'full';
        }

        return 'over';
    }

    public function getTtAmountStatusLabelAttribute(): string
    {
        return match ($this->tt_amount_status) {
            'no_tt' => 'Belum Ada TT',
            'partial' => 'Sebagian',
            'full' => 'Penuh',
            'over' => 'Melebihi Total',
            default => '-',
        };
    }

    public function isAvailableForTt(): bool
    {
        return $this->tt_remaining_nominal > 0;
    }
}
