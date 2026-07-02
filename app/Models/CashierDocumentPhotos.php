<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

class CashierDocumentPhotos extends Model
{
    protected $fillable = [
        'cashier_document_id',
        'photo_path',
        'sort_order',
    ];

    public function document()
    {
        return $this->belongsTo(CashierDocuments::class, 'cashier_document_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        // Sesuaikan dengan cara kamu generate URL Google Drive
        // Contoh: kalau photo_path = fileId
        // return "https://drive.google.com/uc?id={$this->photo_path}&export=view";

        // Atau kalau pakai satu disk:
        return \Storage::disk('public')->url($this->photo_path);
    }
}
