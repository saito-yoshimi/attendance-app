<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ログイン画面が表示される(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_管理者ログインできる(): void
    {
        $admin = User::factory()->create([
            'admin_status' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
    }

    public function test_メール未入力(): void
    {
        $response = $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => '',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_パスワード未入力(): void
    {
        $response = $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'test@example.com',
                'password' => '',
            ]);

        $response->assertSessionHasErrors('password');
    }
}
