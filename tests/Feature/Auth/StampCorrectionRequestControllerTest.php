<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampCorrectionRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーの申請一覧を表示できる()
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);
    }

    public function test_承認待ち申請が表示される()
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/stamp_correction_request/list');

        $response->assertSee('承認待ち');
    }

    public function test_申請詳細を表示できる()
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $application = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/application/{$application->id}");
        $response->assertStatus(200);
    }

    public function test_承認待ち申請詳細を表示できる()
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $application = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/application/{$application->id}");

        $response
            ->assertStatus(200)
            ->assertViewHas('hasPendingRequest');
    }
}
