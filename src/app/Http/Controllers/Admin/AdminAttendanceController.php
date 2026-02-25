<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\Admin\Attendance\AdminAttendanceUpdateRequest;

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

    public function show($id)
    {
        $attendance = Attendance::with([
            'user',
            'breakTimes',   // 休憩
            'correctionRequest' // 承認待ち判定用
        ])->findOrFail($id);

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
        ]);
    }
    
    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // ① 出勤・退勤・備考の更新
        $attendance->clock_in  = $attendance->work_date . ' ' . $request->clock_in;
        $attendance->clock_out = $attendance->work_date . ' ' . $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        // ② 既存の休憩時間を削除
        $attendance->breakTimes()->delete();

        // ③ 新しい休憩時間を登録（複数対応）
        if ($request->break_start) {
            foreach ($request->break_start as $index => $start) {

                // 空行はスキップ
                if (!$start && !$request->break_end[$index]) {
                    continue;
                }

                $attendance->breakTimes()->create([
                    'break_in'  => $attendance->work_date . ' ' . $start,
                    'break_out' => $request->break_end[$index]
                        ? $attendance->work_date . ' ' . $request->break_end[$index]
                        : null,
                ]);
            }
        }

        return back()->with('success', '修正しました');
    }

    public function staffMonthly(Request $request, $id)
    {
        // 1. 対象ユーザー取得
        $user = User::findOrFail($id);

        // 2. 表示する月を決定（?month=2023-06 のように受け取る）
        $currentMonth = $request->query('month', now()->format('Y-m'));

        // Carbon で月初・月末を作成
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth   = Carbon::parse($currentMonth)->endOfMonth();

        // 3. 勤怠データ取得（その月の全日分）
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        // 4. Blade に渡す
        return view('admin.attendance.staff_monthly', [
            'user'          => $user,
            'attendances'   => $attendances,
            'currentMonth'  => $currentMonth,
            'prevMonth'     => Carbon::parse($currentMonth)->subMonth()->format('Y-m'),
            'nextMonth'     => Carbon::parse($currentMonth)->addMonth()->format('Y-m'),
        ]);
    }
}


