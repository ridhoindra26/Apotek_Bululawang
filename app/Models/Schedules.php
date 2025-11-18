<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedules extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable = ['id_branch', 'id_shift_time','id_employee','date','shift','is_vacation'];

    public function branches()  {
        return $this->belongsTo(Branches::class, 'id_branch');
    }
    public function employees(){
        return $this->belongsTo(Employees::class, 'id_employee');
    }

    public function shiftTime(){
        return $this->belongsTo(ShiftTimes::class, 'id_shift_time'); 
    }

    /** ─────────────── Scopes (optional) ─────────────── */
    public function scopeForEmployee($q, int $employeeId){ 
        return $q->where('id_employee', $employeeId); 
    }

    public function scopeOnDate($q, $date){ 
        return $q->whereDate('date', $date); 
    }

}
