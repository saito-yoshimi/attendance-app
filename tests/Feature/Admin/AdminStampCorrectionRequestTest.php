<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 承認待ち一覧が表示される
     */
    public function test_承認待ち一覧が表示される()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);
    }

    /**
     * 修正申請詳細が表示される
     */
    public function test_修正申請詳細が表示される()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                "/stamp_correction_request/approve/{$request->id}"
            );

        $response->assertStatus(200);
    }

    /**
     * 修正申請を承認できる
     */
    public function test_修正申請を承認できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $attendanceRecord =
            AttendanceRecord::factory()->create([
                'user_id' => $user->id,
            ]);

        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendanceRecord->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(
                "/stamp_correction_request/approve/{$request->id}"
            );

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'stamp_correction_requests',
            [
                'id' => $request->id,
                'status' => 'approved',
            ]
        );
    }
}
