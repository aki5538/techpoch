<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\Admin\Attendance\AdminAttendanceUpdateRequest;
use App\Models\User;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    public function show($id)
    {
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'correctionRequests'
        ])->findOrFail($id);

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
        ]);
    }
    
    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // 承認待ちなら更新禁止（仕様書 FN038）
        if ($attendance->correctionRequests()->where('status', 'pending')->exists()) {
            return back()->withErrors(['error' => '承認待ちのため修正はできません。']);
        }

        $attendance->clock_in  = $attendance->work_date . ' ' . $request->clock_in;
        $attendance->clock_out = $attendance->work_date . ' ' . $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        $attendance->breakTimes()->delete();

        if ($request->break_start_1 || $request->break_end_1) {
            $attendance->breakTimes()->create([
                'break_in'  => $request->break_start_1
                    ? $attendance->work_date . ' ' . $request->break_start_1
                    : null,
                'break_out' => $request->break_end_1
                    ? $attendance->work_date . ' ' . $request->break_end_1
                    : null,
            ]);
        }

        if ($request->break_start_2 || $request->break_end_2) {
            $attendance->breakTimes()->create([
                'break_in'  => $request->break_start_2
                    ? $attendance->work_date . ' ' . $request->break_start_2
                    : null,
                'break_out' => $request->break_end_2
                    ? $attendance->work_date . ' ' . $request->break_end_2
                    : null,
            ]);
        }

        $attendance->load('breakTimes');

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

        return back()->with('success', '修正しました');
    }

    public function staffMonthly(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->query('month', now()->format('Y-m'));

        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth   = Carbon::parse($currentMonth)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereDate('work_date', '>=', $startOfMonth->toDateString())
            ->whereDate('work_date', '<=', $endOfMonth->toDateString())
            ->orderBy('work_date', 'asc')
            ->get();

        return view('admin.attendance.staff_monthly', [
            'user'          => $user,
            'attendances'   => $attendances,
            'currentMonth'  => $currentMonth,
            'prevMonth'     => Carbon::parse($currentMonth)->subMonth()->format('Y-m'),
            'nextMonth'     => Carbon::parse($currentMonth)->addMonth()->format('Y-m'),
        ]);
    }

    public function staffMonthlyCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->query('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth   = Carbon::parse($currentMonth)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date', 'asc')
            ->get();

        $csvHeader = [
            '日付', '出勤', '退勤', '休憩', '合計'
        ];

        $csvData = [];
        foreach ($attendances as $attendance) {
            $csvData[] = [
                $attendance->work_date,
                $attendance->clock_in ?? '',
                $attendance->clock_out ?? '',
                $attendance->break_time ?? '',
                $attendance->working_time ?? '',
            ];
        }

        $fileName = "{$user->name}_{$currentMonth}_attendance.csv";

        return response()->streamDownload(function () use ($csvHeader, $csvData) {
            $stream = fopen('php://output', 'w');

            fprintf($stream, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($stream, $csvHeader);

            foreach ($csvData as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $fileName);
    }
}


