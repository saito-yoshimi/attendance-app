<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_管理者が勤怠詳細画面を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/{$record->id}");

        $response->assertStatus(200);
    }

    public function test_承認待ち申請がある勤怠詳細を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        StampCorrectionRequest::factory()->create([
            'attendance_record_id' => $record->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/{$record->id}");

        $response
            ->assertStatus(200)
            ->assertViewHas('hasPendingRequest');
    }

    public function test_管理者が勤怠を更新できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(
                "/admin/attendance/{$record->id}",
                [
                    'new_clock_in' => '09:00',
                    'new_clock_out' => '18:00',
                    'comment' => '管理者修正',

                    'new_break_in' => [
                        '12:00',
                    ],

                    'new_break_out' => [
                        '13:00',
                    ],
                ]
            );

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'attendance_records',
            [
                'id' => $record->id,
                'comment' => '管理者修正',
            ]
        );
    }

    public function test_管理者更新時に休憩が再登録される()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $record->id,
        ]);

        $this
            ->actingAs($admin)
            ->put(
                "/admin/attendance/{$record->id}",
                [
                    'new_clock_in' => '09:00',
                    'new_clock_out' => '18:00',
                    'comment' => '更新',

                    'new_break_in' => [
                        '12:00',
                    ],

                    'new_break_out' => [
                        '13:00',
                    ],
                ]
            );

        $this->assertDatabaseHas(
            'breaks',
            [
                'attendance_record_id' => $record->id,
                'break_in' => '12:00',
            ]
        );
    }
}
