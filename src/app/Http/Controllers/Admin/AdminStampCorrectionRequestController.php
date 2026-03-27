<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

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

        return view('admin.stamp_correction_request.pg12_list', compact('pending', 'approved'));
    }

    public function approve($attendance_correct_request_id)
    {
        // 修正申請データを取得（勤怠・休憩も含む）
        $request = StampCorrectionRequest::with(['attendance', 'attendance.breakTimes'])
            ->findOrFail($attendance_correct_request_id);

        // PG13 の画面を表示
        return view('admin.stamp_correction_request.detail', compact('request'));
    }

    public function updateApprove(Request $req, $attendance_correct_request_id)
    {
        // ① 修正申請を取得
        $request = StampCorrectionRequest::with(['attendance', 'attendance.breakTimes'])
            ->findOrFail($attendance_correct_request_id);

        // ② ステータス更新
        $request->status = 'approved';
        $request->save();

        // ③ 勤怠データ更新
        $attendance = $request->attendance;

        $attendance->clock_in  = $attendance->work_date . ' ' . $request->clock_in;
        $attendance->clock_out = $attendance->work_date . ' ' . $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        // ④ 休憩再登録
        $attendance->breakTimes()->delete();

        if (!empty($request->break_start)) {
            foreach ($request->break_start as $index => $start) {

                if (!$start && empty($request->break_end[$index])) {
                    continue;
                }

                $attendance->breakTimes()->create([
                    'break_in'  => $attendance->work_date . ' ' . $start,
                    'break_out' => !empty($request->break_end[$index])
                        ? $attendance->work_date . ' ' . $request->break_end[$index]
                        : null,
                ]);
            }
        }

        // ⑤ 休憩合計
        $breakSeconds = 0;
        foreach ($attendance->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $breakSeconds += Carbon::parse($break->break_out)
                    ->diffInSeconds(Carbon::parse($break->break_in));
            }
        }
        $attendance->break_time = floor($breakSeconds / 60);

        // ⑥ 実働時間
        if ($attendance->clock_in && $attendance->clock_out) {
            $workSeconds = Carbon::parse($attendance->clock_out)
                ->diffInSeconds(Carbon::parse($attendance->clock_in));

            $attendance->working_time = floor(($workSeconds - $breakSeconds) / 60);
        }

        $attendance->save();

        // ⑦ 一覧に戻る
        return redirect()->route('admin.stamp_correction_request.list')
            ->with('success', '承認しました');
    }
}


