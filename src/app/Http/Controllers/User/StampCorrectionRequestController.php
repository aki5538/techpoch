<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Http\Requests\User\StampCorrectionRequest as StampCorrectionRequestForm;


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

        // バリデーション済みデータを取得
        $data = $request->validated();

        StampCorrectionRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendanceId,
            'reason'        => $data['note'],
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