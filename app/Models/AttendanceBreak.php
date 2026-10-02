<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];

    /**
     * 勤怠情報
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceRecord::class,
            'attendance_record_id'
        );
    }
}
