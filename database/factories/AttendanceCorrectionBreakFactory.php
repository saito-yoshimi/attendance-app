<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionBreakFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stamp_correction_request_id' => StampCorrectionRequest::factory(),

            'break_in' => '12:00:00',

            'break_out' => '13:00:00',
        ];
    }
}
