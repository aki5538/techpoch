<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class ClockController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        // ステータス判定
        if (!$attendance) {
            $status = '勤務外';
        } else {
            $status = $attendance->status;
        }

        // 日付と時刻（UI と同じ形式）
        $weekMap = [
            'Sun' => '日',
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
        ];

        $w = $weekMap[Carbon::now()->format('D')];

        $today = Carbon::now()->format("Y年n月j日") . "({$w})";
        $now   = Carbon::now()->format('H:i');

        return view('user.attendance.clock', [
            'status' => $status,
            'today'  => $today,
            'now'    => $now,
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠が既にある場合は出勤不可
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        if ($attendance) {
            return redirect()->route('user.attendance');
        }

        // 勤怠レコード作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $todayDate,
            'clock_in'  => Carbon::now(),
            'status'    => '出勤中',
        ]);

        return redirect()->route('user.attendance');
    }

    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        // 出勤中でなければ休憩入できない
        if (!$attendance || $attendance->status !== '出勤中') {
            return redirect()->route('user.attendance');
        }

        // 休憩レコードを作成（何回でもOK）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in'      => Carbon::now(),
        ]);

        // ステータスを休憩中に変更
        $attendance->update([
            'status' => '休憩中',
        ]);

        return redirect()->route('user.attendance');
    }

    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        // 勤怠が無い or 出勤中でない場合は戻す
        if (!$attendance || $attendance->status !== '休憩中') {
            return redirect()->route('user.attendance');
        }

        // break_out が null の最新レコードを取得
        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_out')
            ->orderBy('break_in', 'desc')
            ->first();

        // 念のため break レコードが無い場合のガード
        if (!$break) {
            return redirect()->route('user.attendance');
        }

        // break_out を保存
        $break->update([
            'break_out' => Carbon::now(),
        ]);

        // ステータスを出勤中に戻す
        $attendance->update([
            'status' => '出勤中',
        ]);

        return redirect()->route('user.attendance');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->with('breakTimes')
            ->first();

        if (!$attendance || $attendance->status !== '出勤中') {
            return redirect()->route('user.attendance');
        }

        // 退勤時刻
        $attendance->clock_out = Carbon::now();
        $attendance->status = '退勤済';

        // ★ 休憩合計
        $breakSeconds = 0;
        foreach ($attendance->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $breakSeconds += Carbon::parse($break->break_out)
                    ->diffInSeconds(Carbon::parse($break->break_in));
            }
        }
        $attendance->break_time = floor($breakSeconds / 60);

        // ★ 実働
        if ($attendance->clock_in && $attendance->clock_out) {
            $workSeconds = Carbon::parse($attendance->clock_out)
                ->diffInSeconds(Carbon::parse($attendance->clock_in));

            $attendance->working_time = floor(($workSeconds - $breakSeconds) / 60);
        }

        $attendance->save();

        return redirect()->route('user.attendance');
    }

    public function show($id)
    {
        // 勤怠データを1件取得
        $attendance = Attendance::with('correctionRequest', 'breakTimes')
        ->where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

        // Blade に渡す
        return view('user.attendance.detail', [
            'attendance' => $attendance,
            'user' => auth()->user(),
        ]);
    }
}
