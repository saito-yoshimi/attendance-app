<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠一覧画面が表示される
     */
    public function test_勤怠一覧画面が表示される()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
    }

    /**
     * 自分の勤怠情報が表示される
     */
    public function test_自分の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertSee(now()->format('m/d'));
    }

    /**
     * 他人の勤怠情報は表示されない
     */
    public function test_他人の勤怠情報は表示されない()
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        AttendanceRecord::factory()->create([
            'user_id' => $otherUser->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertDontSee(now()->format('m/d'));
    }

    /**
     * 現在月が表示される
     */
    public function test_現在月が表示される()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee(
            now()->format('Y/m')
        );
    }

    /**
     * 詳細リンクが表示される
     */
    public function test_詳細リンクが表示される()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertSee(
            route(
                'attendance.show',
                $attendanceRecord->id
            ),
            false
        );
    }
}
