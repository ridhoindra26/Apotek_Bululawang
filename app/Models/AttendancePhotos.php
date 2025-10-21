<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendancePhotos extends Model
{
    use HasFactory;

    protected $table = 'attendance_photos';

    protected $fillable = [
        'id_attendance_event',
        'disk',
        'path',
        'mime',
        'size_kb',
        'width',
        'height',
        'hash',
    ];

    protected $casts = [
        'size_kb' => 'integer',
        'width'   => 'integer',
        'height'  => 'integer',
    ];

    /** Relationships */
    public function event() { return $this->belongsTo(AttendanceEvents::class, 'id_attendance_event'); }

    /** Helpers */
    public function url(): string
    {
        // assumes you're storing on a public disk
        return \Storage::disk($this->disk)->url($this->path);
    }
}
