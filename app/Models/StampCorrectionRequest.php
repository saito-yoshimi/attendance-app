<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'comment',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 勤怠
     */
    public function attendanceRecord()
    {
        return $this->belongsTo(
            AttendanceRecord::class,
            'attendance_record_id'
        );
    }

    /**
     * 承認者（管理者）
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 修正申請時の休憩情報
     */
    public function proposalBreaks()
    {
        return $this->hasMany(
            AttendanceCorrectionBreak::class,
            'stamp_correction_request_id'
        );
    }
}
