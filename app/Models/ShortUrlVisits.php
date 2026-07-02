<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrlVisits extends Model
{
    protected $fillable = [
        'short_url_id',
        'ip_address',
        'user_agent',
        'referer',
        'source_app',
    ];

    public function shortUrl(): BelongsTo
    {
        return $this->belongsTo(ShortUrls::class, 'short_url_id');
    }
}
