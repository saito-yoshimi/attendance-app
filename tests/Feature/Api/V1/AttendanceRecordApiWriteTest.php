<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceRecordApiWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠を作成できる()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            '/api/v1/attendance-records',
            [
                'date' => now()->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas(
            'attendance_records',
            [
                'user_id' => $user->id,
            ]
        );
    }

    public function test_勤怠作成時にバリデーションエラー()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            '/api/v1/attendance-records',
            []
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'date',
        ]);
    }

    public function test_勤怠を更新できる()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_out' => null,
        ]);

        $response = $this->putJson(
            "/api/v1/attendance-records/{$record->id}",
            [
                'clock_out' => '18:00:00',
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'attendance_records',
            [
                'id' => $record->id,
                'clock_out' => '18:00:00',
            ]
        );
    }

    public function test_存在しない勤怠更新は404()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(
            '/api/v1/attendance-records/99999',
            [
                'clock_out' => '18:00:00',
            ]
        );

        $response->assertStatus(404);
    }

    public function test_勤怠を削除できる()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$record->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing(
            'attendance_records',
            [
                'id' => $record->id,
            ]
        );
    }

    public function test_存在しない勤怠削除は404()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            '/api/v1/attendance-records/99999'
        );

        $response->assertStatus(404);
    }
}
