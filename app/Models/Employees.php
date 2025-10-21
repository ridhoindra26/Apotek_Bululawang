<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employees extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = ['name', 'id_branch', 'default_pagi_shift_time_id', 'default_siang_shift_time_id', 'id_role', 'date_of_birth', 'date_start'];

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
    
    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function defaultPagiShift(){ 
        return $this->belongsTo(ShiftTimes::class, 'default_pagi_shift_time_id');
    }

    public function defaultSiangShift(){ 
        return $this->belongsTo(ShiftTimes::class, 'default_siang_shift_time_id'); 
    }

}
