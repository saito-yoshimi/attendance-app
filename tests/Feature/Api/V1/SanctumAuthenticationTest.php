<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証時は401
     */
    public function test_未認証ユーザーは勤怠を作成できない()
    {
        $response = $this->postJson(
            '/api/v1/attendance-records',
            [
                'date' => now()->toDateString(),
                'clock_in' => '09:00:00',
            ]
        );

        $response->assertStatus(401);
    }

    /**
     * 未認証時は更新できない
     */
    public function test_未認証ユーザーは勤怠を更新できない()
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            "/api/v1/attendance-records/{$record->id}",
            [
                'clock_out' => '18:00:00',
            ]
        );

        $response->assertStatus(401);
    }

    /**
     * 未認証時は削除できない
     */
    public function test_未認証ユーザーは勤怠を削除できない()
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$record->id}"
        );

        $response->assertStatus(401);
    }

    /**
     * 自分の勤怠は更新できる
     */
    public function test_認証済みユーザーは自分の勤怠を更新できる()
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

    /**
     * 自分の勤怠は削除できる
     */
    public function test_認証済みユーザーは自分の勤怠を削除できる()
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

    /**
     * 他人の勤怠は更新できない
     */
    public function test_他人の勤怠は更新できない()
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $record = AttendanceRecord::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->putJson(
            "/api/v1/attendance-records/{$record->id}",
            [
                'clock_out' => '18:00:00',
            ]
        );

        $response->assertStatus(403);
    }

    /**
     * 他人の勤怠は削除できない
     */
    public function test_他人の勤怠は削除できない()
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $record = AttendanceRecord::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$record->id}"
        );

        $response->assertStatus(403);
    }
}
