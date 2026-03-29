<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Http\Requests\User\StampCorrectionRequest as StampCorrectionRequestForm;
use App\Models\AttendanceCorrectRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $pending = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.stamp_correction_request.list', compact('pending', 'approved'));
    }

    public function store(StampCorrectionRequestForm $request, $attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);

        // ★ validated() を all() に変更
        $data = $request->all();

        $requestModel = AttendanceCorrectRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendanceId,
            'clock_in'      => $data['clock_in'],
            'clock_out'     => $data['clock_out'],
            'reason'        => $data['note'],
            'status'        => 'pending',
        ]);

        // 休憩1
        if (!empty($data['break1_in']) || !empty($data['break1_out'])) {
            $requestModel->correctBreakTimes()->create([
                'break_in'  => $data['break1_in'],
                'break_out' => $data['break1_out'],
            ]);
        }

        // 休憩2
        if (!empty($data['break2_in']) || !empty($data['break2_out'])) {
            $requestModel->correctBreakTimes()->create([
                'break_in'  => $data['break2_in'],
                'break_out' => $data['break2_out'],
            ]);
        }

        return redirect()->route('stamp_correction_request.list');
    }
    
    public function detail(Request $request, $attendanceId)
    {
        $status = $request->status;

        return view('user.attendance.detail', compact('attendance', 'status'));
    }
}