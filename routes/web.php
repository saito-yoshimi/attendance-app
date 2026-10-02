<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/**
 * ログアウト時
 */
Route::get('/', function () {
    return redirect('/login');
});

Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
})->name('admin.logout');

/**
 * 一般
 */
Route::middleware(['auth', 'verified'])->group(function () {

    // 勤怠作成
    Route::get(
        '/attendance',
        [AttendanceController::class, 'create']
    )->name('attendance.create');

    // 勤怠登録
    Route::post(
        '/attendance',
        [AttendanceController::class, 'store']
    )->name('attendance.store');

    // 勤怠一覧
    Route::get(
        '/attendance/list',
        [AttendanceController::class, 'index']
    )->name('attendance.list');

    // 勤怠一覧＞勤怠詳細
    Route::get(
        '/attendance/detail/{attendanceRecord}',
        [AttendanceController::class, 'show']
    )->name('attendance.show');

    // 勤怠一覧＞勤怠詳細＞修正
    Route::post(
        '/stamp_correction_request/store',
        [StampCorrectionRequestController::class, 'store']
    )->name('stamp_correction_request.store');

    // 申請（admin：申請一覧）
    Route::get(
        '/stamp_correction_request/list',
        [StampCorrectionRequestController::class, 'index']
    )->name('stamp_correction_request.list');

    // 申請詳細
    Route::get(
        '/application/{application}',
        [StampCorrectionRequestController::class, 'show']
    )->name('stamp_correction_request.show');

    // レポート
    Route::get(
        '/attendance/report',
        [AttendanceReportController::class, 'report']
    )->name('attendance.report');

});

/**
 * 管理者ログイン画面
 */
Route::get(
    '/admin/login',
    [AdminLoginController::class, 'create']
)->name('admin.login');

Route::post(
    '/admin/login',
    [AdminLoginController::class, 'store']
)->name('admin.login.store');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {

    // 勤怠一覧
    Route::get(
        '/attendance/list',
        [AdminAttendanceController::class, 'index']
    )->name('admin.attendance.index');

    // 勤怠一覧＞詳細
    Route::get(
        '/attendance/{attendanceRecord}',
        [AdminAttendanceController::class, 'show']
    )->name('admin.attendance.show');

    // 勤怠一覧＞詳細>CSV出力
    Route::put(
        '/attendance/{attendanceRecord}',
        [AdminAttendanceController::class, 'update']
    )->name('admin.attendance.update');

    // スタッフ一覧
    Route::get(
        '/staff/list',
        [AdminStaffController::class, 'index']
    )->name('admin.staff.list');

    // スタッフ一覧＞詳細
    Route::get(
        '/attendance/staff/{id}',
        [AdminStaffController::class, 'attendanceList']
    )->name('admin.staff.attendance');

    // CSV出力
    Route::post(
        '/export',
        [AdminStaffController::class, 'export']
    )->name('admin.staff.attendance.export');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    // 申請一覧＞詳細
    Route::get(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'show']
    )->name('admin.application.show');

    // 申請一覧＞詳細承認
    Route::post(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'approve']
    )->name('admin.application.approve');

});
