<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrls extends Model
{
    protected $fillable = [
        'title',
        'original_url',
        'short_code',
        'custom_slug',
        'is_active',
        'expires_at',
        'click_count',
        'qr_code_path',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'expires_at'  => 'datetime',
        'click_count' => 'integer',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(ShortUrlVisits::class, 'short_url_id');
    }

    public function getRouteCodeAttribute(): string
    {
        return $this->custom_slug ?: $this->short_code;
    }

    public function getPublicUrlAttribute(): string
    {
        return rtrim(config('shortener.public_domain'), '/') . '/s/' . $this->route_code;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->qr_code_path, '/'));
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeResolvable($query)
    {
        return $query->active()->notExpired();
    }
}
