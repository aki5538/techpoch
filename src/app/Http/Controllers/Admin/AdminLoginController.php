<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    /**
     * ログイン画面表示（GET）
     */
    public function showLoginForm()
    {
        return view('auth.admin.login');
    }

    /**
     * ログイン処理（POST）
     */
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 認証試行（管理者ガード）
        if (!Auth::guard('admin')->attempt($credentials)) {
            return back()
                ->withInput()
                ->withErrors([
                    'login' => 'ログイン情報が登録されていません',
                ]);
        }

        // ログイン成功 → 管理者勤怠一覧へ
        return redirect()->intended('/admin/attendance/list');
    }
}