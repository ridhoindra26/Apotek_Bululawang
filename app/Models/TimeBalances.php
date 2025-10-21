<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeBalances extends Model
{
    use HasFactory;

    protected $table = 'time_balances';

    protected $fillable = ['id_employee','debt_minutes','credit_minutes'];

    public function employee() { return $this->belongsTo(Employees::class, 'id_employee'); }

    public function getNetMinutesAttribute(): int
    {
        return ($this->credit_minutes ?? 0) - ($this->debt_minutes ?? 0);
    }

    public function getNetReadableAttribute(): string
    {
        $net = $this->net_minutes;
        $sign = $net >= 0 ? '+' : '-';
        $hours = floor(abs($net) / 60);
        $mins  = abs($net) % 60;
        return sprintf('%s%02dh %02dm', $sign, $hours, $mins);
    }
}
