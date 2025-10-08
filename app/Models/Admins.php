<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;

class Admins extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'username',
        'password',
    ];
}
