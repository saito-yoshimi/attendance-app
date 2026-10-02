<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_メール形式不正()
    {
        $response = $this
            ->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'aaa',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_メール未入力()
    {
        $response = $this
            ->from('/admin/login')
            ->post('/admin/login', [
                'email' => '',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_パスワード未入力()
    {
        $response = $this
            ->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => '',
            ]);

        $response->assertSessionHasErrors('password');
    }
}
