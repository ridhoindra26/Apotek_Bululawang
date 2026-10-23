<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuggestionAttachment extends Model
{
    use HasFactory;

    public const PRIVATE_DISK = 'local';

    protected $fillable = [
        'suggestion_id',
        'disk',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function suggestion()
    {
        return $this->belongsTo(Suggestion::class);
    }
}
