<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧（PG04）
     */
    public function list(Request $request)
    {
        $user = auth()->user();

        // 現在の月（例：2026-03）
        $currentMonth = $request->query('month', now()->format('Y-m'));

        // 前月・翌月
        $prevMonth = \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        // Blade が使う「2026/03」形式のラベル
        $currentMonthLabel = \Carbon\Carbon::parse($currentMonth)->format('Y/m');

        // 勤怠データ取得
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->where('work_date', 'like', $currentMonth . '%')
            ->orderBy('work_date', 'desc')
            ->get();

        return view('user.attendance.list', compact(
            'attendances',
            'currentMonth',
            'currentMonthLabel',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function detail(Request $request, $id)
    {
        // 勤怠データ取得
        $attendance = Attendance::with('breakTimes', 'correctionRequests')->findOrFail($id);

        // ログイン中のユーザー
        $user = auth()->user();

        // 申請一覧から渡された status
        $status = $request->status;

        return view('user.attendance.detail', compact('attendance', 'user', 'status'));
    }
}