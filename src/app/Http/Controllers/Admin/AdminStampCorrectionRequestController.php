<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;


class AdminStampCorrectionRequestController extends Controller
{
    /** PG12：修正申請一覧（管理者） */
    public function list()
    {
        // 承認待ち（pending）
        $pending = StampCorrectionRequest::where('status', 'pending')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 承認済み（approved）
        $approved = StampCorrectionRequest::where('status', 'approved')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request.list', compact('pending', 'approved'));
    }

    public function approve($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($id);

        // 承認済みなら detail.blade を表示
        if ($request->status === 'approved') {
            return view('admin.stamp_correction_request.detail', compact('request'));
        }

        // 承認待ちなら approve.blade を表示
        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    public function approvePost($id)
    {
        // 修正申請を取得
        $request = StampCorrectionRequest::with(['attendance'])
            ->findOrFail($id);

        // ① 修正申請のステータスを approved に更新
        $request->status = 'approved';
        $request->save();

        // ② 勤怠情報を修正申請内容で更新
        $attendance = $request->attendance;

        $attendance->clock_in  = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->break1_start = $request->break1_start;
        $attendance->break1_end   = $request->break1_end;
        $attendance->break2_start = $request->break2_start;
        $attendance->break2_end   = $request->break2_end;
        $attendance->note = $request->note;

        $attendance->save();

        // ③ 承認後、同じ URL に戻る（承認済み画面を表示）
        return redirect()->route('stamp_correction_request.approve', $id);
    }
}


