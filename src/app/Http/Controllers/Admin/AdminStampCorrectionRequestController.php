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
}


