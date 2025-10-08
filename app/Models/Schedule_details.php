<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule_details extends Model
{
    use HasFactory;

    protected $table = 'Schedule_details';

    protected $fillable = ['id_schedule', 'id_employee', 'date'];

    public function employees()
    {
        return $this->belongsTo(Employees::class, 'id_employee');
    }

    public function Schedules()
    {
        return $this->belongsTo(Schedules::class, 'id_schedule');
    }
}
