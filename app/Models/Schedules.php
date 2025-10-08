<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedules extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable = ['branch_id','employee_id','date','shift','is_leave'];

    public function branches()  {
        return $this->belongsTo(Branches::class, 'id_branch');
    }
    public function employees(){
        return $this->belongsTo(Employees::class, 'id_employee');
    }
}
