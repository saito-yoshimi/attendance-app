<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'comment',
    ];

    /**
     * ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩
     */
    public function breaks()
    {
        return $this->hasMany(
            AttendanceBreak::class,
            'attendance_record_id'
        );
    }

    /**
     *勤怠修正申請
     */
    public function correctionRequests(): HasMany
    {
        return $this->hasMany(
            StampCorrectionRequest::class,
            'attendance_record_id'
        );
    }

    public function applications()
    {
        return $this->hasMany(
            StampCorrectionRequest::class,
            'attendance_record_id'
        );
    }
}
