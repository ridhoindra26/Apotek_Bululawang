<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Greetings extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'id_type',
    ];

    public function type()
    {
        return $this->belongsTo(GreetingTypes::class, 'id_type');
    }
}