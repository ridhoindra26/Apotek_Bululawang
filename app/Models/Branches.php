<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branches extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = ['name'];

    public function employees()
    {
        return $this->hasMany(Employees::class, 'id_branch');
    }

    public function schedules()
    {
        return $this->hasMany(Schedules::class, 'id_branch');
    }
}
