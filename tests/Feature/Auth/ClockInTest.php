<?php

namespace Tests\Feature\Auth;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤できる()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'attendance_records',
            [
                'user_id' => $user->id,
            ]
        );
    }

    public function test_1日に2回出勤できない()
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $this
            ->actingAs($user)
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $this->assertEquals(
            1,
            AttendanceRecord::count()
        );
    }
}
