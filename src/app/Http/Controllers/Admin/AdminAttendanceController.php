<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 日付（指定がなければ今日）
        $date = $request->input('date', Carbon::today()->toDateString());

        // その日の勤怠を全ユーザー分取得
        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }
}


