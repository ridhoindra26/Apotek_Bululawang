<?php

namespace App\Services\ShortUrl;

use App\Models\ShortUrls;
use Illuminate\Support\Str;

class ShortUrlCodeGenerator
{
    public function generate(int $length = 6): string
    {
        do {
            $code = Str::lower(Str::random($length));
        } while ($this->exists($code));

        return $code;
    }

    protected function exists(string $code): bool
    {
        return ShortUrls::query()
            ->where('short_code', $code)
            ->orWhere('custom_slug', $code)
            ->exists();
    }
}