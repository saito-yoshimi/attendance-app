<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面が表示される
     */
    public function test_勤怠詳細画面が表示される()
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendanceRecord->id}");

        $response->assertStatus(200);
    }

    /**
     * 氏名が表示される
     */
    public function test_氏名が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendanceRecord->id}");

        $response->assertSee('テスト太郎');
    }

    /**
     * 日付が表示される
     */
    public function test_日付が表示される()
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-09-28',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendanceRecord->id}");

        $response->assertSee('2026年');
        $response->assertSee('09月28日');
    }

    /**
     * 出勤・退勤時刻が表示される
     */
    public function test_出退勤時刻が表示される()
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendanceRecord->id}");

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 休憩時間が表示される
     */
    public function test_休憩時間が表示される()
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendanceRecord->id}");

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
