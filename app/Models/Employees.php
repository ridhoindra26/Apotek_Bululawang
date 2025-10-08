<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employees extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = ['name', 'id_branch', 'id_role', 'date_of_birth', 'date_start'];

    public function branches()
    {
        return $this->belongsTo(Branches::class, 'id_branch');
    }

    public function roles()
    {
        return $this->belongsTo(Roles::class, 'id_role');
    }

    public function schedule_details()
    {
        return $this->hasMany(Schedule_details::class, 'id_employee');
    }

    public function vacations()
    {
        return $this->hasMany(Vacations::class, 'id_employee');
    }
}
