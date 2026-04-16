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

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        if (!$attendance) {
            $status = '勤務外';
        } else {
            $status = $attendance->status;
        }

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

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        if ($attendance) {
            return redirect()->route('user.attendance');
        }

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

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        if (!$attendance || $attendance->status !== '出勤中') {
            return redirect()->route('user.attendance');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in'      => Carbon::now(),
        ]);

        $attendance->update([
            'status' => '休憩中',
        ]);

        return redirect()->route('user.attendance');
    }

    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        if (!$attendance || $attendance->status !== '休憩中') {
            return redirect()->route('user.attendance');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_out')
            ->orderBy('break_in', 'desc')
            ->first();

        if (!$break) {
            return redirect()->route('user.attendance');
        }

        $break->update([
            'break_out' => Carbon::now(),
        ]);

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

        $attendance->clock_out = Carbon::now();
        $attendance->status = '退勤済';

        $breakSeconds = 0;
        foreach ($attendance->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $breakSeconds += Carbon::parse($break->break_out)
                    ->diffInSeconds(Carbon::parse($break->break_in));
            }
        }
        $attendance->break_time = floor($breakSeconds / 60);

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
        $attendance = Attendance::with('correctionRequest', 'breakTimes')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('user.attendance.detail', [
            'attendance' => $attendance,
            'user' => auth()->user(),
        ]);
    }
}
