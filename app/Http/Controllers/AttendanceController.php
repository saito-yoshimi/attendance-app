<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面
     */
    public function create(): View
    {

        $user = auth()->user();

        $today = Carbon::today();

        $attendanceRecord = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendanceRecord) {
            $user->attendance_status = '勤務外';
        } elseif ($attendanceRecord->clock_out) {
            $user->attendance_status = '退勤済';
        } elseif (
            $attendanceRecord->breaks()
                ->whereNull('break_out')
                ->exists()
        ) {
            $user->attendance_status = '休憩中';
        } else {
            $user->attendance_status = '出勤中';
        }

        $formattedDate = Carbon::now()
            ->locale('ja')
            ->translatedFormat('Y年n月j日(D)');

        $formattedTime = Carbon::now()
            ->format('H:i');

        return view(
            'user.attendance-register',
            compact(
                'user',
                'formattedDate',
                'formattedTime'
            )
        );
    }

    /**
     * 勤怠一覧
     */
    public function index(Request $request)
    {
        // 管理者用
        if (Auth::user()->admin_status) {
            return redirect()->route('admin.attendance.index');
        }

        // 一般用
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::now();

        $previousMonth = $date->copy()
            ->subMonth()
            ->format('Y-m');

        $nextMonth = $date->copy()
            ->addMonth()
            ->format('Y-m');

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', auth()->id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        $formattedAttendanceRecords = $attendanceRecords->map(
            function ($attendanceRecord) {

                $totalBreakMinutes =
                    $attendanceRecord->breaks->sum(
                        function ($break) {

                            if (! $break->break_out) {
                                return 0;
                            }

                            return Carbon::parse(
                                $break->break_out
                            )->diffInMinutes(
                                Carbon::parse(
                                    $break->break_in
                                )
                            );
                        }
                    );

                $totalBreakTime = sprintf(
                    '%02d:%02d:00',
                    floor($totalBreakMinutes / 60),
                    $totalBreakMinutes % 60
                );

                $totalTime = null;

                if (
                    $attendanceRecord->clock_in &&
                    $attendanceRecord->clock_out
                ) {
                    $workMinutes =
                        Carbon::parse(
                            $attendanceRecord->clock_out
                        )->diffInMinutes(
                            Carbon::parse(
                                $attendanceRecord->clock_in
                            )
                        ) - $totalBreakMinutes;

                    $totalTime = sprintf(
                        '%02d:%02d:00',
                        floor($workMinutes / 60),
                        $workMinutes % 60
                    );
                }

                return [
                    'id' => $attendanceRecord->id,

                    'date' => Carbon::parse(
                        $attendanceRecord->date
                    )->format('m/d'),

                    'clock_in' => $attendanceRecord->clock_in
                        ? Carbon::parse(
                            $attendanceRecord->clock_in
                        )->format('H:i')
                        : '',

                    'clock_out' => $attendanceRecord->clock_out
                        ? Carbon::parse(
                            $attendanceRecord->clock_out
                        )->format('H:i')
                        : '',

                    'total_break_time' => $totalBreakTime,

                    'total_time' => $totalTime,
                ];
            }
        );

        return view(
            'user.user-attendance-list',
            compact(
                'date',
                'previousMonth',
                'nextMonth',
                'formattedAttendanceRecords'
            )
        );
    }

    /**
     * 勤怠詳細
     */
    public function show(AttendanceRecord $attendanceRecord): View|RedirectResponse
    {
        abort_if($attendanceRecord->user_id !== auth()->id(), 403);

        // 修正申請が pending のものが存在するか判定
        $hasPendingRequest = StampCorrectionRequest::where(
            'attendance_record_id',
            $attendanceRecord->id
        )
            ->where('status', 'pending')
            ->exists();

        $attendanceRecord->load(['user', 'breaks']);

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
            'breaks' => $attendanceRecord->breaks->map(function ($break) {
                return [
                    'break_in' => $break->break_in
                        ? Carbon::parse($break->break_in)->format('H:i')
                        : '',
                    'break_out' => $break->break_out
                        ? Carbon::parse($break->break_out)->format('H:i')
                        : '',
                ];
            })->toArray(),
        ];

        return view('user.user-detail', [
            'data' => $data,
            'user' => $attendanceRecord->user,
            'hasPendingRequest' => $hasPendingRequest,
        ]);
    }

    /**
     * 勤怠登録
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $today = Carbon::today();

        $attendanceRecord = AttendanceRecord::where(
            'user_id',
            $user->id
        )
            ->whereDate('date', $today)
            ->first();

        switch ($request->action) {

            case 'clock_in':

                if ($attendanceRecord) {

                    return redirect()
                        ->route('attendance.create')
                        ->with('error', '本日は既に出勤しています。');
                }

                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'clock_in' => now()->format('H:i:s'),
                ]);

                break;

            case 'break_in':

                if ($attendanceRecord) {

                    AttendanceBreak::create([
                        'attendance_record_id' => $attendanceRecord->id,
                        'break_in' => now()->format('H:i:s'),
                    ]);
                }

                break;

            case 'break_out':

                if ($attendanceRecord) {

                    $break = AttendanceBreak::where(
                        'attendance_record_id',
                        $attendanceRecord->id
                    )
                        ->whereNull('break_out')
                        ->latest()
                        ->first();

                    if ($break) {
                        $break->update([
                            'break_out' => now()->format('H:i:s'),
                        ]);
                    }
                }

                break;

            case 'clock_out':

                if (
                    $attendanceRecord &&
                    is_null($attendanceRecord->clock_out)
                ) {
                    $attendanceRecord->update([
                        'clock_out' => now()->format('H:i:s'),
                    ]);
                }

                break;
        }

        return redirect()->route('attendance.create');
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}
