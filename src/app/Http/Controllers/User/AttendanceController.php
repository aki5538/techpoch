<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧（PG04）
     */
    public function list()
    {
        $user = auth()->user();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('user.attendance.list', compact('attendances'));
    }
}