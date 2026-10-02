<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤できる
     */
    public function test_退勤できる()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => null,
            ]);

        $response = $this
            ->actingAs($user)
            ->post('/attendance', [
                'action' => 'clock_out',
            ]);

        $response->assertRedirect();

        $attendanceRecord->refresh();

        $this->assertNotNull(
            $attendanceRecord->clock_out
        );
    }

    /**
     * 退勤後は退勤時刻が保存される
     */
    public function test_退勤時刻が保存される()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'clock_in' => '09:00:00',
            ]);

        $this
            ->actingAs($user)
            ->post('/attendance/clock-out');

        $attendanceRecord->refresh();

        $this->assertNotNull(
            $attendanceRecord->clock_out
        );
    }
}
