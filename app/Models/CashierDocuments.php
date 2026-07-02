<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashierDocuments extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'cashier_id',
        'type',
        'date',
        'shift',
        'description',
        'photo_path',
        'status',
        'confirmed_by',
        'confirmed_at',
        'admin_note',
    ];

    protected $casts = [
        'date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function cashier()
    {
        return $this->belongsTo(Employees::class, 'cashier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branches::class, 'branch_id');
    }

    // Helper label type
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'closing_cash' => 'Kertas Tutup Kasir',
            'deposit_slip' => 'Bukti Setoran',
            'blood_check'  => 'Bukti Cek Darah',
            'petty_cash'   => 'Foto Kas Kecil',
            default        => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu',
            'confirmed' => 'Terkonfirmasi',
            'rejected'  => 'Ditolak',
        };
    }

    public function photos()
    {
        return $this->hasMany(CashierDocumentPhotos::class, 'cashier_document_id')
                    ->orderBy('sort_order')
                    ->orderBy('id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $first = $this->photos->first();
        
        if ($first) {
            return $first->url;
        }

        if (!empty($this->photo_path ?? null)) {
            return \Storage::disk('public')->url($this->photo_path);
        }

        return null;
        // return "google.com";
    }

    public function getPhotoUrlsAttribute()
    {
        return $this->photos->map->url->filter()->values();
    }
}
