<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vacations extends Model
{
    use HasFactory;

    public const TYPE_CUTI  = 'cuti';
    public const TYPE_LIBUR = 'libur';
    public const TYPE_ABSENT  = 'absent';

    public const TYPES = [
        self::TYPE_CUTI  => 'Cuti',
        self::TYPE_LIBUR => 'Libur',
        self::TYPE_ABSENT  => 'Tidak Masuk',
    ];

    protected $table = 'vacations';

    protected $fillable = ['id_employee', 'date_of_vacation', 'type', 'description'];

    public function employees()
    {
        return $this->belongsTo(Employees::class, 'id_employee');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
