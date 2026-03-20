<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function list()
    {
        // 一般ユーザーのみ取得
        $users = User::where('role', 0)->get();

        return view('admin.staff.list', compact('users'));
    }
}
