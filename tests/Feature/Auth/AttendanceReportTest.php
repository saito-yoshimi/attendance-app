<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストはアクセスできない()
    {
        $this
            ->get('/attendance/report')
            ->assertRedirect('/login');
    }

    public function test_勤怠データがなくても表示できる()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/attendance/report');

        $response->assertStatus(200);
    }

    public function test_認証ユーザーのレポートを表示できる()
    {
        $user = User::factory()->create();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDays(2)->toDateString(),
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/attendance/report');

        $response
            ->assertStatus(200)
            ->assertViewHas('summary')
            ->assertViewHas('monthly');
    }
}
