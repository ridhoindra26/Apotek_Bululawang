<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employees extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'name',
        'id_branch',
        'default_pagi_shift_time_id',
        'default_siang_shift_time_id',
        'id_role',
        'date_of_birth',
        'date_start',

        // payroll fields
        'base_salary',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'payroll_email',
        'payroll_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_start' => 'date',
        'base_salary' => 'integer',
        'payroll_active' => 'boolean',
    ];


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

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_employee', 'id_employee', 'announcement_id')
            ->withTimestamps();
    }

    public function payroll_items()
    {
        return $this->hasMany(PayrollItem::class, 'id_employee');
    }


}
