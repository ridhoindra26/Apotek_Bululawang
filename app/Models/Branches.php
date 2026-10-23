<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branches extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = ['name'];

    public function employees()
    {
        return $this->hasMany(Employees::class, 'id_branch');
    }

    public function schedules()
    {
        return $this->hasMany(Schedules::class, 'id_branch');
    }

    public function supplierTtInvoices()
    {
        return $this->hasMany(SupplierTtInvoice::class, 'branch_id');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class, 'branch_id');
    }

    public function supplierTtPaymentBatches()
    {
        return $this->hasMany(SupplierTtPaymentBatch::class, 'branch_id');
    }

    public function targetedSuggestions()
    {
        return $this->hasMany(Suggestion::class, 'target_branch_id');
    }
}
