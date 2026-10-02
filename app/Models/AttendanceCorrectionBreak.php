<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrectionBreak extends Model
{
    protected $fillable = [
        'stamp_correction_request_id',
        'break_in',
        'break_out',
    ];

    public function correctionRequest(): BelongsTo
    {
        return $this->belongsTo(
            StampCorrectionRequest::class,
            'stamp_correction_request_id'
        );
    }
}
