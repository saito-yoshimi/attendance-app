<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::where('admin_status', false)
            ->orderBy('name')
            ->get();

        return view(
            'admin.staff-list',
            compact('users')
        );
    }

    public function attendanceList(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::now();

        $previousMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        $formattedAttendanceRecords = $attendanceRecords->map(function ($record) {

            $totalBreakMinutes = $record->breaks->sum(function ($break) {

                if (! $break->break_in || ! $break->break_out) {
                    return 0;
                }

                return Carbon::parse($break->break_out)
                    ->diffInMinutes(
                        Carbon::parse($break->break_in)
                    );
            });

            $totalWorkMinutes = 0;

            if ($record->clock_in && $record->clock_out) {

                $totalWorkMinutes =
                    Carbon::parse($record->clock_out)
                        ->diffInMinutes(
                            Carbon::parse($record->clock_in)
                        )
                    - $totalBreakMinutes;
            }

            return [
                'id' => $record->id,
                'date' => Carbon::parse($record->date)->format('m/d'),

                'clock_in' => $record->clock_in
                    ? Carbon::parse($record->clock_in)->format('H:i')
                    : '',

                'clock_out' => $record->clock_out
                    ? Carbon::parse($record->clock_out)->format('H:i')
                    : '',

                'total_break_time' => sprintf(
                    '%d:%02d',
                    floor($totalBreakMinutes / 60),
                    $totalBreakMinutes % 60
                ),

                'total_time' => sprintf(
                    '%d:%02d',
                    floor($totalWorkMinutes / 60),
                    $totalWorkMinutes % 60
                ),
            ];
        });

        return view(
            'admin.staff-attendance-list',
            compact(
                'user',
                'date',
                'previousMonth',
                'nextMonth',
                'formattedAttendanceRecords'
            )
        );
    }

    // CSV出力
    public function export(Request $request)
    {
        $userId = $request->user_id;
        $yearMonth = $request->year_month; // 例: 2024-09

        // 対象ユーザー
        $user = User::findOrFail($userId);

        // 月初と月末
        $start = Carbon::parse($yearMonth.'-01')->startOfMonth();
        $end = Carbon::parse($yearMonth.'-01')->endOfMonth();

        // 勤怠データ取得
        $records = AttendanceRecord::with('breaks')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();

        // CSV データ作成
        $csvData = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩合計', '勤務合計'];

        foreach ($records as $record) {

            // 休憩合計
            $totalBreakMinutes = $record->breaks->sum(function ($break) {
                if (! $break->break_out) {
                    return 0;
                }

                return Carbon::parse($break->break_out)
                    ->diffInMinutes(
                        Carbon::parse($break->break_in)
                    );
            });

            $totalBreakTime = sprintf(
                '%d:%02d',
                floor($totalBreakMinutes / 60),
                $totalBreakMinutes % 60
            );

            // 勤務合計
            $totalWorkTime = '';

            if ($record->clock_in && $record->clock_out) {

                $workMinutes =
                    Carbon::parse($record->clock_out)
                        ->diffInMinutes(
                            Carbon::parse($record->clock_in)
                        ) - $totalBreakMinutes;

                $totalWorkTime = sprintf(
                    '%d:%02d',
                    floor($workMinutes / 60),
                    $workMinutes % 60
                );
            }

            $csvData[] = [
                Carbon::parse($record->date)->format('Y/m/d'),
                $record->clock_in
                    ? Carbon::parse($record->clock_in)->format('H:i')
                    : '',
                $record->clock_out
                    ? Carbon::parse($record->clock_out)->format('H:i')
                    : '',
                $totalBreakTime,
                $totalWorkTime,
            ];
        }

        // StreamedResponse で CSV を返す
        $response = new StreamedResponse(function () use ($csvData) {
            $handle = fopen('php://output', 'w');

            foreach ($csvData as $line) {
                fputcsv($handle, $line);
            }

            fclose($handle);
        });

        // ファイル名
        $filename = 'attendance_'.$yearMonth.'_'.$userId.'.csv';

        // ヘッダー設定
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return response()->streamDownload(function () use ($csvData) {
            $handle = fopen('php://output', 'w');

            // ★ Excel の文字化け防止：UTF-8 BOM を追加
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($csvData as $line) {
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, "attendance_{$yearMonth}_{$userId}.csv");
    }
}
