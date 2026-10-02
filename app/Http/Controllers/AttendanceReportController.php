<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceReportController extends Controller
{
    public function report()
    {
        $user = Auth::user();

        // 過去6ヶ月の範囲
        // $end = Carbon::today();
        $end = Carbon::now()->endOfMonth();
        $start = Carbon::today()->subMonths(5)->startOfMonth();

        // 勤怠データ取得
        $records = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        // 月別初期化（過去6ヶ月分を必ず作る）
        $monthly = [];
        $cursor = $start->copy()->startOfMonth();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');
            $monthly[$key] = [
                'work' => 0,
                'overtime' => 0,
                'late_count' => 0,
                'early_leave_count' => 0,
                'long_work_count' => 0,
            ];
            $cursor->addMonth();
        }

        // サマリー
        $summary = [
            'total_work' => 0,
            'total_overtime' => 0,
            'average_work' => 0,
            'days' => 0,
        ];

        foreach ($records as $record) {

            $monthKey = Carbon::parse($record->date)->format('Y-m');

            // 休憩合計
            $breakMinutes = $record->breaks->sum(function ($break) {
                if (! $break->break_out) {
                    return 0;
                }

                return Carbon::parse($break->break_out)
                    ->diffInMinutes(Carbon::parse($break->break_in));
            });

            // 勤務時間
            if ($record->clock_in && $record->clock_out) {
                $workMinutes = Carbon::parse($record->clock_out)
                    ->diffInMinutes(Carbon::parse($record->clock_in)) - $breakMinutes;
            } else {
                $workMinutes = 0;
            }

            // 残業（8時間 = 480分）
            $overtimeMinutes = max(0, $workMinutes - 480);

            // 月別集計
            $monthly[$monthKey]['work'] += $workMinutes;
            $monthly[$monthKey]['overtime'] += $overtimeMinutes;

            // 遅刻判定（その日の 09:00 を超えたら）
            if ($record->clock_in) {
                $startTime = Carbon::parse($record->date.' 09:00');
                $clockInTime = Carbon::parse($record->date.' '.$record->clock_in);

                if ($clockInTime->gt($startTime)) {
                    $monthly[$monthKey]['late_count']++;
                }
            }

            // 早退判定（その日の 18:00 より前なら）
            if ($record->clock_out) {
                $endTime = Carbon::parse($record->date.' 18:00');
                $clockOutTime = Carbon::parse($record->date.' '.$record->clock_out);

                if ($clockOutTime->lt($endTime)) {
                    $monthly[$monthKey]['early_leave_count']++;
                }
            }

            // 長時間労働（10時間 = 600分）
            if ($workMinutes > 600) {
                $monthly[$monthKey]['long_work_count']++;
            }

            // サマリー
            $summary['total_work'] += $workMinutes;
            $summary['total_overtime'] += $overtimeMinutes;
            $summary['days']++;
        }

        // 平均労働時間（1日あたり）
        $summary['average_work'] = $summary['days'] > 0
            ? round($summary['total_work'] / $summary['days'], 1)
            : 0;

        return view('user.attendance-report', compact(
            'user',
            'summary',
            'monthly'
        ));
    }
}
