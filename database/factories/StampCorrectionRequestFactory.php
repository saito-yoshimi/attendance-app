<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StampCorrectionRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attendance_record_id' => AttendanceRecord::factory(),

            'user_id' => User::factory(),

            'requested_clock_in' => '08:00:00',

            'requested_clock_out' => '17:00:00',

            'comment' => '打刻間違いのため修正申請',

            'status' => 'pending',

            'approved_at' => null,

            'approved_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(function () {
            return [
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => User::factory(),
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(function () {
            return [
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ];
        });
    }
}
