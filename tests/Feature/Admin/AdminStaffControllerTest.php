<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_前月の勤怠一覧を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $staff = User::factory()->create([
            'admin_status' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?date=2026-08");

        $response->assertStatus(200);
    }

    public function test_翌月の勤怠一覧を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $staff = User::factory()->create([
            'admin_status' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?date=2026-10");

        $response->assertStatus(200);
    }

    public function test_cs_v出力できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $staff = User::factory()->create([
            'admin_status' => false,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date' => '2026-09-01',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(
                '/admin/export',
                [
                    'user_id' => $staff->id,
                    'year_month' => '2026-09',
                ]
            );

        $response->assertOk();

        $response->assertHeader(
            'content-disposition'
        );
    }
}
