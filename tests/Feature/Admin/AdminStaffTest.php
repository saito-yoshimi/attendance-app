<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_スタッフ一覧画面を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        User::factory()->count(3)->create([
            'admin_status' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);
    }

    public function test_スタッフ一覧に一般ユーザーが表示される()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $staff = User::factory()->create([
            'name' => '山田太郎',
            'admin_status' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertSee('山田太郎');
    }

    public function test_スタッフ別勤怠一覧を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $staff = User::factory()->create([
            'admin_status' => false,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}");

        $response->assertStatus(200);
    }
}
