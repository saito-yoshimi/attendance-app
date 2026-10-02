<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminStampCorrectionRequestController extends Controller
{
    // 申請一覧
    public function index(): View
    {
        $applications = StampCorrectionRequest::with([
            'user',
            'attendanceRecord',
            'proposalBreaks',
        ])->latest()->get();

        return view(
            'admin.admin-application-list',
            compact('applications')
        );
    }

    // 申請詳細
    public function show(
        int $attendance_correct_request_id
    ): View {

        $application = StampCorrectionRequest::with([
            'user',
            'attendanceRecord',
            'proposalBreaks',
        ])->findOrFail(
            $attendance_correct_request_id
        );

        $user = $application->user;

        return view(
            'admin.admin-application-detail',
            compact(
                'application',
                'user'
            )
        );
    }

    // 承認
    public function approve(int $attendance_correct_request_id): RedirectResponse
    {
        $application = StampCorrectionRequest::with('proposalBreaks')
            ->findOrFail($attendance_correct_request_id);

        DB::transaction(function () use ($application) {

            $attendanceRecord = AttendanceRecord::findOrFail(
                $application->attendance_record_id
            );

            $attendanceRecord->update([
                'clock_in' => $application->requested_clock_in,
                'clock_out' => $application->requested_clock_out,
                'comment' => $application->comment,
            ]);

            // 既存休憩を削除
            AttendanceBreak::where(
                'attendance_record_id',
                $attendanceRecord->id
            )->delete();

            // 休憩申請を登録
            foreach ($application->proposalBreaks as $break) {

                AttendanceBreak::create([
                    'attendance_record_id' => $attendanceRecord->id,
                    'break_in' => $break->break_in,
                    'break_out' => $break->break_out,
                ]);
            }

            $application->update([
                'status' => 'approved',
                'approved_at' => Carbon::now(),
                'approved_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route(
                'admin.application.show',
                $application->id
            )
            ->with(
                'success',
                '申請を承認しました。'
            );
    }
}
