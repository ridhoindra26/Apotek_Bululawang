<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollTransferTemplate extends Model
{
    protected $fillable = [
        'name',
        'delimiter',
        'columns_json',
        'encoding',
        'include_header',
    ];

    protected $casts = [
        'columns_json' => 'array',
        'include_header' => 'boolean',
    ];
}
