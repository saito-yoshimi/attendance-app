<?php

namespace App\Http\Controllers;

use App\Http\Requests\StampCorrectionRequestRequest;
use App\Models\AttendanceCorrectionBreak;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        if (Auth::user()->admin_status) {
            $applications = StampCorrectionRequest::with('user')
                ->latest()
                ->get();

            return view('admin.admin-application-list', compact('applications'));
        }

        $user = Auth::user();

        $applications = StampCorrectionRequest::with('attendanceRecord')
            ->where('user_id', $user->id)
            ->get();

        $formattedApplications = $applications->map(function ($application) {
            return [
                'id' => $application->id,
                'approval_status' => $application->status === 'pending'
                    ? '承認待ち'
                    : '承認済み',

                'date' => Carbon::parse($application->attendanceRecord->date)->format('Y/m/d'),
                'comment' => $application->comment,
                'application_date' => optional($application->created_at)->format('Y/m/d'),
            ];
        });

        return view('user.user-application-list', compact('user', 'formattedApplications'));
    }

    public function show(StampCorrectionRequest $application)
    {
        $application->load([
            'attendanceRecord',
            'proposalBreaks',
            'user',
        ]);

        $record = $application->attendanceRecord;

        $data = [
            'id' => $record->id,
            'year' => Carbon::parse($record->date)
                ->format('Y年'),

            'date' => Carbon::parse($record->date)
                ->format('m月d日'),

            'clock_in' => $application->requested_clock_in
                ? Carbon::parse(
                    $application->requested_clock_in
                )->format('H:i')
                : '',

            'clock_out' => $application->requested_clock_out
                ? Carbon::parse(
                    $application->requested_clock_out
                )->format('H:i')
                : '',

            'comment' => $application->comment,

            'breaks' => $application->proposalBreaks
                ->map(function ($break) {
                    return [
                        'break_in' => $break->break_in
                            ? Carbon::parse(
                                $break->break_in
                            )->format('H:i')
                            : '',

                        'break_out' => $break->break_out
                            ? Carbon::parse(
                                $break->break_out
                            )->format('H:i')
                            : '',
                    ];
                })
                ->toArray(),
        ];

        return view('user.user-detail', [
            'data' => $data,
            'user' => $application->user,
            'hasPendingRequest' => $application->status === 'pending',
        ]);
    }

    public function store(
        StampCorrectionRequestRequest $request
    ): RedirectResponse {

        $validated = $request->validated();
        DB::transaction(function () use ($validated) {

            $stampCorrectionRequest = StampCorrectionRequest::create([
                'attendance_record_id' => $validated['attendance_record_id'],
                'user_id' => Auth::id(),
                'requested_clock_in' => $validated['new_clock_in'],
                'requested_clock_out' => $validated['new_clock_out'] ?? null,
                'comment' => $validated['comment'],
                'status' => 'pending',
            ]);

            // 休憩情報登録
            foreach ($validated['new_break_in'] as $index => $breakIn) {

                $breakOut =
                    $validated['new_break_out'][$index] ?? null;

                // 両方空の場合は登録しない
                if (empty($breakIn) && empty($breakOut)) {
                    continue;
                }

                AttendanceCorrectionBreak::create([
                    'stamp_correction_request_id' => $stampCorrectionRequest->id,
                    'break_in' => $breakIn,
                    'break_out' => $breakOut,
                ]);
            }
        });

        return redirect()
            ->route('attendance.list')
            ->with(
                'success',
                '修正申請を登録しました。'
            );
    }
}
