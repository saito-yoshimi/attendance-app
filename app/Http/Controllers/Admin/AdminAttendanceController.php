<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\AdminAttendanceUpdateRequest;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    /**
     * 管理者勤怠一覧
     */
    public function index(Request $request): View
    {
        $users = User::all();

        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $previousDay = $date->copy()->subDay()->format('Y-m-d');
        $nextDay = $date->copy()->addDay()->format('Y-m-d');

        $attendanceRecords = AttendanceRecord::with(['user', 'breaks'])
            ->whereDate('date', $date)
            ->orderBy('clock_in')
            ->get();

        // ★ ここで計算する（Blade に書いてはいけない）
        foreach ($attendanceRecords as $record) {

            // 休憩合計
            $totalBreakMinutes = $record->breaks->sum(function ($break) {
                if (! $break->break_out) {
                    return 0;
                }

                return Carbon::parse($break->break_out)
                    ->diffInMinutes(Carbon::parse($break->break_in));
            });

            $record->total_break_time = sprintf(
                '%02d:%02d:00',
                floor($totalBreakMinutes / 60),
                $totalBreakMinutes % 60
            );

            // 勤務合計
            if ($record->clock_in && $record->clock_out) {
                $workMinutes = Carbon::parse($record->clock_out)
                    ->diffInMinutes(Carbon::parse($record->clock_in))
                    - $totalBreakMinutes;

                $record->total_time = sprintf(
                    '%02d:%02d:00',
                    floor($workMinutes / 60),
                    $workMinutes % 60
                );
            } else {
                $record->total_time = null;
            }
        }

        return view('admin.admin-attendance-list', compact(
            'users',
            'attendanceRecords',
            'date',
            'previousDay',
            'nextDay'
        ));
    }

    /**
     * 勤怠詳細画面
     */
    public function show(AttendanceRecord $attendanceRecord): View
    {
        $attendanceRecord->load(['user', 'breaks']);
        $user = $attendanceRecord->user;

        // ★ 休憩を整形
        $formattedBreaks = $attendanceRecord->breaks->map(function ($break) {
            return [
                'break_in' => $break->break_in
                    ? Carbon::parse($break->break_in)->format('H:i')
                    : '',
                'break_out' => $break->break_out
                    ? Carbon::parse($break->break_out)->format('H:i')
                    : '',
            ];
        })->toArray();

        // ★ 勤怠データを整形
        $data = [
            'id' => $attendanceRecord->id,
            'year' => Carbon::parse($attendanceRecord->date)->format('Y年'),
            'date' => Carbon::parse($attendanceRecord->date)->format('m月d日'),
            'clock_in' => $attendanceRecord->clock_in
                ? Carbon::parse($attendanceRecord->clock_in)->format('H:i')
                : '',
            'clock_out' => $attendanceRecord->clock_out
                ? Carbon::parse($attendanceRecord->clock_out)->format('H:i')
                : '',
            'comment' => $attendanceRecord->comment,
            'breaks' => $formattedBreaks,
        ];

        // ★ 承認待ち判定
        $hasPendingRequest = StampCorrectionRequest::where(
            'attendance_record_id',
            $attendanceRecord->id
        )
            ->where('status', 'pending')
            ->exists();

        // ★ Blade に渡すのは data のみ（attendanceRecord は使わない）
        return view('admin.admin-detail', [
            'attendanceRecord' => $data,
            'user' => $user,
            'hasPendingRequest' => $hasPendingRequest,
        ]);
    }

    /**
     * 勤怠修正
     */
    public function update(
        AdminAttendanceUpdateRequest $request,
        AttendanceRecord $attendanceRecord
    ): RedirectResponse {
        DB::transaction(function () use ($request, $attendanceRecord) {

            $attendanceRecord->update([
                'clock_in' => $request->new_clock_in,
                'clock_out' => $request->new_clock_out,
                'comment' => $request->comment,
            ]);

            // 既存休憩を削除
            AttendanceBreak::where(
                'attendance_record_id',
                $attendanceRecord->id
            )->delete();

            // 休憩を再登録
            foreach ($request->new_break_in as $index => $breakIn) {

                $breakOut = $request->new_break_out[$index] ?? null;

                // 両方空ならスキップ
                if (empty($breakIn) && empty($breakOut)) {
                    continue;
                }

                AttendanceBreak::create([
                    'attendance_record_id' => $attendanceRecord->id,
                    'break_in' => $breakIn,
                    'break_out' => $breakOut,
                ]);
            }
        });

        return redirect()
            ->route(
                'admin.attendance.show',
                $attendanceRecord->id
            )
            ->with(
                'success',
                '勤怠情報を更新しました。'
            );
    }
}
