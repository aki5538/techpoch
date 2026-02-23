<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        // 承認待ち
        $pending = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 承認済み
        $approved = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.stamp_correction_request.list', compact('pending', 'approved'));
    }

    public function store($attendanceId)
    {
        StampCorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendanceId,
            'reason' => 'ユーザーによる修正申請',
            'status' => 'pending',
        ]);

        return redirect()->route('stamp_correction_request.list');
    }
}
