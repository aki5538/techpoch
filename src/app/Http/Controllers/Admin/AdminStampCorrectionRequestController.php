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
        // ① 修正申請を取得（勤怠・休憩も取得）
        $request = StampCorrectionRequest::with(['attendance', 'attendance.breakTimes'])
            ->findOrFail($id);

        // ② 修正申請のステータスを approved に更新
        $request->status = 'approved';
        $request->save();

        // ③ 対象の勤怠データを取得
        $attendance = $request->attendance;

        // ④ 出勤・退勤・備考を更新
        $attendance->clock_in  = $attendance->work_date . ' ' . $request->clock_in;
        $attendance->clock_out = $attendance->work_date . ' ' . $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        // ⑤ 既存の休憩レコードを削除
        $attendance->breakTimes()->delete();

        // ⑥ 新しい休憩レコードを登録
        if (!empty($request->break_start)) {
            foreach ($request->break_start as $index => $start) {

                // 空行はスキップ
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

        // ⑦ 休憩合計（分）を再計算
        $breakSeconds = 0;
        foreach ($attendance->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $breakSeconds += Carbon::parse($break->break_out)
                    ->diffInSeconds(Carbon::parse($break->break_in));
            }
        }
        $attendance->break_time = floor($breakSeconds / 60);

        // ⑧ 実働時間（分）を再計算
        if ($attendance->clock_in && $attendance->clock_out) {
            $workSeconds = Carbon::parse($attendance->clock_out)
                ->diffInSeconds(Carbon::parse($attendance->clock_in));

            $attendance->working_time = floor(($workSeconds - $breakSeconds) / 60);
        }

        $attendance->save();

        // ⑨ 承認後、同じ画面に戻る
        return redirect()->route('stamp_correction_request.approve', $id)
            ->with('success', '承認しました');
    }
}


