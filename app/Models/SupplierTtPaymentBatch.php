<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierTtPaymentBatch extends Model
{
    use HasFactory;

    protected $fillable = ['batch_number', 'branch_id', 'payment_date', 'total_nominal', 'status', 'notes', 'locked_at', 'locked_by', 'paid_at', 'paid_by'];
    protected $casts = ['payment_date' => 'date', 'total_nominal' => 'decimal:2', 'locked_at' => 'datetime', 'paid_at' => 'datetime'];

    public function branch() { return $this->belongsTo(Branches::class, 'branch_id'); }
    public function invoices() { return $this->belongsToMany(SupplierTtInvoice::class, 'supplier_tt_payment_batch_items', 'payment_batch_id', 'supplier_tt_invoice_id')->withPivot('nominal_snapshot')->withTimestamps(); }
    public function supplierAccountSnapshots() { return $this->hasMany(SupplierTtPaymentBatchSupplierAccount::class, 'payment_batch_id'); }
    public function locker() { return $this->belongsTo(User::class, 'locked_by'); }
    public function payer() { return $this->belongsTo(User::class, 'paid_by'); }
    public function getStatusLabelAttribute(): string { return match ($this->status) { 'draft' => 'Draft', 'locked' => 'Dikunci', 'paid' => 'Dibayar', default => ucfirst($this->status) }; }
}
