<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $user = auth()->user();

        $currentMonth = $request->query('month', now()->format('Y-m'));

        $prevMonth = \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        $currentMonthLabel = \Carbon\Carbon::parse($currentMonth)->format('Y/m');

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
        $attendance = Attendance::with(['breakTimes', 'correctionRequests'])->findOrFail($id);

        $user = auth()->user();

        $latestRequest = $attendance->correctionRequests()->latest()->first();

        $status = $request->status;

        return view('user.attendance.detail', compact('attendance', 'user', 'status', 'latestRequest'));
    }
}