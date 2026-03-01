<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ClockController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:user'])->group(function () {

    // 打刻画面（仕様書 PG03）
    Route::get('/attendance', [ClockController::class, 'index'])->name('user.attendance');

    // 出勤
    Route::post('/attendance/clock-in', [ClockController::class, 'clockIn'])->name('user.clock.in');

    // 休憩入
    Route::post('/attendance/break-in', [ClockController::class, 'breakIn'])->name('user.break.in');

    // 休憩戻
    Route::post('/attendance/break-out', [ClockController::class, 'breakOut'])->name('user.break.out');

    // 退勤
    Route::post('/attendance/clock-out', [ClockController::class, 'clockOut'])->name('user.clock.out');

    // 勤怠一覧（PG04）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('user.attendance.list');

    // 勤怠詳細（PG05）
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('user.attendance.detail');
    Route::post('/attendance/detail/{id}/correction',[StampCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_request.store');
    // 申請一覧（PG06）
    Route::get('/stamp_correction_request/list', [App\Http\Controllers\User\StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');
});

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm']);
Route::post('/admin/login', [AdminLoginController::class, 'login']);

Route::middleware(['auth:admin'])->group(function () {
    
     // PG11：スタッフ別月次勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffMonthly'])
        ->name('admin.attendance.staff.monthly');
    Route::get('/admin/attendance/staff/{id}/csv', 
        [AdminAttendanceController::class, 'staffMonthlyCsv'])
        ->name('admin.attendance.staff.csv');
        
        // PG08：勤怠一覧
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');
    // PG09：勤怠詳細（表示）
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.detail');

    // PG09：勤怠詳細（修正）
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    // PG10
    Route::get('/admin/staff/list', [AdminStaffController::class, 'list'])
    ->name('admin.staff.list');
    
    // PG12：申請一覧画面（管理者）
    Route::get('/stamp_correction_request/list',
        [AdminStampCorrectionRequestController::class, 'list']
    )->name('stamp_correction_request.list');
    
    // PG13：修正申請承認画面（管理者）
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'approve']
    )->name('stamp_correction_request.approve');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'approvePost']
    )->name('stamp_correction_request.approvePost');
});



