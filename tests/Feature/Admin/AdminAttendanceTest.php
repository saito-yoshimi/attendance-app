<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が勤怠一覧画面を表示できる
     */
    public function test_管理者が勤怠一覧画面を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
    }

    /**
     * 全ユーザーの勤怠情報が表示される
     */
    public function test_全ユーザーの勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertSee('テスト太郎');
    }

    /**
     * 当日日付が表示される
     */
    public function test_当日日付が表示される()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertSee(
            now()->format('Y/m/d')
        );
    }

    /**
     * 前日データが表示できる
     */
    public function test_前日勤怠一覧を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/attendance/list?date='.
                now()->subDay()->toDateString());

        $response->assertStatus(200);
    }

    /**
     * 翌日データが表示できる
     */
    public function test_翌日勤怠一覧を表示できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/attendance/list?date='.
                now()->addDay()->toDateString());

        $response->assertStatus(200);
    }
}
