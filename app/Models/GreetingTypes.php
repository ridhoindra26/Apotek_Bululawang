<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GreetingTypes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function greetings()
    {
        return $this->hasMany(Greetings::class, 'id_type');
    }
}