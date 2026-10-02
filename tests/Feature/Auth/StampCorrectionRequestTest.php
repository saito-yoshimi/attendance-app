<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠修正申請ができる()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        $response = $this
            ->actingAs($user)
            ->post('/stamp_correction_request/store', [
                'attendance_record_id' => $attendanceRecord->id,
                'new_clock_in' => '08:00',
                'new_clock_out' => '17:00',
                'comment' => '打刻修正申請',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'stamp_correction_requests',
            [
                'attendance_record_id' => $attendanceRecord->id,
                'user_id' => $user->id,
                'comment' => '打刻修正申請',
                'status' => 'pending',
            ]
        );
    }

    public function test_修正申請は承認待ちで作成される()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        $this
            ->actingAs($user)
            ->post('/stamp_correction_request/store', [
                'attendance_record_id' => $attendanceRecord->id,
                'new_clock_in' => '08:00',
                'new_clock_out' => '17:00',
                'comment' => '修正申請',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
            ]);

        $this->assertDatabaseHas(
            'stamp_correction_requests',
            [
                'status' => 'pending',
            ]
        );
    }

    public function test_修正申請時に休憩情報も保存される()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        $this
            ->actingAs($user)
            ->post('/stamp_correction_request/store', [
                'attendance_record_id' => $attendanceRecord->id,
                'new_clock_in' => '08:00',
                'new_clock_out' => '17:00',
                'comment' => '修正申請',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
            ]);

        $this->assertDatabaseHas(
            'attendance_correction_breaks',
            [
                'break_in' => '12:00',
                'break_out' => '13:00',
            ]
        );
    }
}
