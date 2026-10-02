<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細を取得できる()
    {
        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        StampCorrectionRequest::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
            'comment' => '修正申請テスト',
        ]);

        $response = $this->getJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.user.id',
                $user->id
            )
            ->assertJsonCount(
                1,
                'data.breaks'
            )
            ->assertJsonCount(
                1,
                'data.applications'
            )
            ->assertJsonPath(
                'data.applications.0.comment',
                '修正申請テスト'
            );
    }

    public function test_存在しない勤怠は404()
    {
        $response = $this->getJson(
            '/api/v1/attendance-records/99999'
        );

        $response->assertStatus(404);
    }
}
