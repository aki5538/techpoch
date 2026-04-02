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
        // 修正申請（休憩レコード含む）を取得
        $requestModel = AttendanceCorrectRequest::with('correctBreakTimes', 'attendance')->findOrFail($attendance_correct_request_id);
        $attendance = $requestModel->attendance;

        // 1. 既存の休憩を削除
        $attendance->breakTimes()->delete();

        // 2. 修正申請の休憩を移植
        foreach ($requestModel->correctBreakTimes as $cbt) {
            $attendance->breakTimes()->create([
                'break_in'  => $cbt->break_in,
                'break_out' => $cbt->break_out,
            ]);
        }

        // 3. 出勤・退勤・備考を更新
        $attendance->update([
            'clock_in'  => $requestModel->clock_in,
            'clock_out' => $requestModel->clock_out,
            'note'      => $requestModel->reason,
        ]);

        // 4. 修正申請のステータスを approved に変更
        $requestModel->update([
            'status' => 'approved',
        ]);

        return redirect()->route('admin.stamp_correction_request.list')->with('success', '承認しました');
    }
}


