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

    // 打刻画面
    Route::get('/clock', [ClockController::class, 'index'])->name('user.clock');

    // 出勤
    Route::post('/clock/in', [ClockController::class, 'clockIn'])->name('user.clock.in');

    // 休憩入
    Route::post('/break/in', [ClockController::class, 'breakIn'])->name('user.break.in');

    // 休憩戻
    Route::post('/break/out', [ClockController::class, 'breakOut'])->name('user.break.out');

    // 退勤
    Route::post('/clock/out', [ClockController::class, 'clockOut'])->name('user.clock.out');
});


