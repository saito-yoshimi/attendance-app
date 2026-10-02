<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 休憩入できる
     */
    public function test_休憩入できる()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
            ]);

        $response = $this
            ->actingAs($user)
            ->post('/attendance', [
                'action' => 'break_in',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'breaks',
            [
                'attendance_record_id' => $attendanceRecord->id,
            ]
        );
    }

    /**
     * 休憩戻できる
     */
    public function test_休憩戻できる()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
            ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/attendance', [
                'action' => 'break_out',
            ]);

        $response->assertRedirect();

        $break->refresh();

        $this->assertNotNull($break->break_out);
    }

    /**
     * 休憩は複数回取得できる
     */
    public function test_休憩は複数回取得できる()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        AttendanceBreak::factory()
            ->count(2)
            ->create([
                'attendance_record_id' => $attendanceRecord->id,
            ]);

        $this->assertEquals(
            2,
            $attendanceRecord
                ->fresh()
                ->breaks()
                ->count()
        );
    }
}
