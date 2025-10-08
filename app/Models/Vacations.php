<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vacations extends Model
{
    use HasFactory;

    protected $table = 'vacations';

    protected $fillable = ['id_employee', 'date_of_vacation', 'description'];

    public function employees()
    {
        return $this->belongsTo(Employees::class, 'id_employee');
    }
}
