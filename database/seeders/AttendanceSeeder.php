<?php

namespace Database\Seeders;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;

        /*
        |--------------------------------------------------------------------------
        | 過去5ヶ月（各月平日15日）
        |--------------------------------------------------------------------------
        */
        for ($m = 5; $m >= 1; $m--) {

            $month = Carbon::now()->subMonths($m);
            $date = $month->copy()->startOfMonth();

            $count = 0;

            while ($count < 15) {

                if ($date->isWeekday()) {

                    $attendance = AttendanceRecord::create([
                        'user_id' => $userId,
                        'date' => $date->toDateString(),
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                        'comment' => '通常勤務',
                    ]);

                    AttendanceBreak::create([
                        'attendance_record_id' => $attendance->id,
                        'break_in' => '12:00:00',
                        'break_out' => '13:00:00',
                    ]);

                    $count++;
                }

                $date->addDay();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 当月データ（17日）
        |--------------------------------------------------------------------------
        */
        $current = Carbon::now()->startOfMonth();

        // 通常勤務 10日
        for ($i = 0; $i < 10; $i++) {

            $attendance = AttendanceRecord::create([
                'user_id' => $userId,
                'date' => $current->copy()->addDays($i)->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => '通常勤務',
            ]);

            AttendanceBreak::create([
                'attendance_record_id' => $attendance->id,
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
            ]);
        }

        // 残業 3日（実働10時間）
        for ($i = 10; $i < 13; $i++) {

            $attendance = AttendanceRecord::create([
                'user_id' => $userId,
                'date' => $current->copy()->addDays($i)->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '20:00:00',
                'comment' => '残業',
            ]);

            AttendanceBreak::create([
                'attendance_record_id' => $attendance->id,
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
            ]);
        }

        // 遅刻 2日（実働7.5時間）
        for ($i = 13; $i < 15; $i++) {

            $attendance = AttendanceRecord::create([
                'user_id' => $userId,
                'date' => $current->copy()->addDays($i)->toDateString(),
                'clock_in' => '09:30:00',
                'clock_out' => '18:00:00',
                'comment' => '遅刻',
            ]);

            AttendanceBreak::create([
                'attendance_record_id' => $attendance->id,
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
            ]);
        }

        // 早退 1日（実働7時間）
        $attendance = AttendanceRecord::create([
            'user_id' => $userId,
            'date' => $current->copy()->addDays(15)->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'comment' => '早退',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        // 長時間労働 1日（実働12時間）
        $attendance = AttendanceRecord::create([
            'user_id' => $userId,
            'date' => $current->copy()->addDays(16)->toDateString(),
            'clock_in' => '08:00:00',
            'clock_out' => '21:00:00',
            'comment' => '長時間労働',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
    }
}
