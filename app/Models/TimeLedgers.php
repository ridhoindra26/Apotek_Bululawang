<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeLedgers extends Model
{
    use HasFactory;

    protected $table = 'time_ledgers';

    protected $fillable = [
        'id_employee','work_date','id_attendance','type','minutes','source','note'
    ];

    protected $casts = [
        'work_date' => 'date',
        'minutes'   => 'integer',
    ];

    public function employee()   { return $this->belongsTo(Employees::class, 'id_employee'); }
    public function attendance() { return $this->belongsTo(Attendances::class, 'id_attendance'); }
}
