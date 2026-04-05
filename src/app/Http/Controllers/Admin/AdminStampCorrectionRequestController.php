<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest; // ← ここ変更
use Carbon\Carbon;

class AdminStampCorrectionRequestController extends Controller
{
    /** PG12：修正申請一覧（管理者） */
    public function list()
    {
        // 承認待ち（pending）
        $pending = AttendanceCorrectRequest::where('status', 'pending') // ← 変更
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 承認済み（approved）
        $approved = AttendanceCorrectRequest::where('status', 'approved') // ← 変更
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request.pg12_list', compact('pending', 'approved'));
    }

    public function approve($attendance_correct_request_id)
    {
        // 修正申請データを取得（勤怠・休憩も含む）
        $request = AttendanceCorrectRequest::with(['attendance', 'attendance.breakTimes']) // ← 変更
            ->findOrFail($attendance_correct_request_id);

        // PG13 の画面を表示
        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    public function updateApprove(Request $req, $attendance_correct_request_id)
    {
       $requestModel = AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

        // ステータスだけ変更（仕様書どおり）
        $requestModel->update([
            'status' => 'approved',
        ]);

        // 遷移しない → 元の画面を再表示する
        return back()->with('success', '承認しました');
    }
}


