<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest;

class AdminStampCorrectionRequestController extends Controller
{
    /** PG12：修正申請一覧（管理者） */
    public function list()
    {
        $pending = AttendanceCorrectRequest::where('status', 'pending')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = AttendanceCorrectRequest::where('status', 'approved')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request.pg12_list', compact('pending', 'approved'));
    }

    public function approve($attendance_correct_request_id)
    {
        $request = AttendanceCorrectRequest::with(['attendance', 'attendance.breakTimes'])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    public function updateApprove(Request $req, $attendance_correct_request_id)
    {
        $requestModel = AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

        $requestModel->update([
            'status' => 'approved',
        ]);

        return back()->with('success', '承認しました');
    }
}


