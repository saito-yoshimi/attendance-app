<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // まず UserSeeder（他のユーザーなど）
        $this->call([
            UserSeeder::class,
        ]);

        // 勤怠ダミーデータ
        $this->call(AttendanceSeeder::class);
    }
}
