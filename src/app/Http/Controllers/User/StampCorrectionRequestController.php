<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;

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

    public function store(Request $request, $attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);

        // 備考は必須
        $request->validate([
            'note' => 'required|string',
        ]);

        StampCorrectionRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendanceId,
            'reason'        => $request->note,  // ← これでOK
            'status'        => 'pending',
        ]);

        return redirect()->route('stamp_correction_request.list');
    }

    public function detail(Request $request, $attendanceId)
    {
        $status = $request->status;

        return view('user.attendance.detail', compact('attendance', 'status'));
    }
}