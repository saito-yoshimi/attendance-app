<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザー
        User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'admin_status' => false,
        ]);

        User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'admin_status' => false,
        ]);

        // 管理者ユーザー
        User::factory()->create([
            'name' => 'ユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'admin_status' => true,
        ]);
    }
}
