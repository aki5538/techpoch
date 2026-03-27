<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\User\ClockController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminStaffController;

/*
|--------------------------------------------------------------------------
| ログアウト
|--------------------------------------------------------------------------
*/

Route::post('/logout', function () {
    Auth::guard('user')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::post('/admin/logout', function () {
    Auth::guard('admin')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/admin/login');
})->name('admin.logout');

/*
|--------------------------------------------------------------------------
| 管理者ログイン
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm']);
Route::post('/admin/login', [AdminLoginController::class, 'login']);

/*
|--------------------------------------------------------------------------
| 一般ユーザー（PG03〜PG06）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:user'])->group(function () {

    // PG03：打刻画面
    Route::get('/attendance', [ClockController::class, 'index'])->name('user.attendance');

    Route::post('/attendance/clock-in', [ClockController::class, 'clockIn'])->name('user.clock.in');
    Route::post('/attendance/break-in', [ClockController::class, 'breakIn'])->name('user.break.in');
    Route::post('/attendance/break-out', [ClockController::class, 'breakOut'])->name('user.break.out');
    Route::post('/attendance/clock-out', [ClockController::class, 'clockOut'])->name('user.clock.out');

    // PG04：勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('user.attendance.list');

    // PG05：勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('user.attendance.detail');

    // 修正申請（PG05 → PG06）
    Route::post('/attendance/detail/{id}/correction', [StampCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_request.store');

    // PG06：申請一覧（一般ユーザー）
    Route::get('/stamp_correction_request/list',
        [StampCorrectionRequestController::class, 'index']
    )->name('stamp_correction_request.list');
});

/*
|--------------------------------------------------------------------------
| 管理者（PG08〜PG13）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin'])
    ->name('admin.')   // ← これが衝突を完全に防ぐ
    ->group(function () {

    // PG11：スタッフ別月次勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffMonthly'])
        ->name('attendance.staff.monthly');

    Route::get('/admin/attendance/staff/{id}/csv',
        [AdminAttendanceController::class, 'staffMonthlyCsv'])
        ->name('attendance.staff.csv');

    // PG08：勤怠一覧
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('attendance.list');

    // PG09：勤怠詳細（表示）
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('attendance.detail');

    // PG09：勤怠詳細（修正）
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('attendance.update');

    // PG10：スタッフ一覧
    Route::get('/admin/staff/list', [AdminStaffController::class, 'list'])
        ->name('staff.list');

    // PG12：申請一覧（管理者）
    Route::get('/stamp_correction_request/list',
        [AdminStampCorrectionRequestController::class, 'list']
    )->name('stamp_correction_request.list');

    // PG13 表示（GET）
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'approve']
    )->name('stamp_correction_request.approve');

    // PG13 承認処理（POST）
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'updateApprove']
    )->name('stamp_correction_request.update');
});

/*
|--------------------------------------------------------------------------
| メール認証
|--------------------------------------------------------------------------
*/

Route::get('/email/verify', function () {
    return view('auth.user.verify-email');
})->middleware(['auth:user'])->name('verification.notice');







