<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ClockController;

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
});


