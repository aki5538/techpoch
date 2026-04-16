<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\User\LoginRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends AuthenticatedSessionController
{
    public function store(Request $request)
    {
        // FormRequest を手動で発動（仕様書 FN008）
        app(LoginRequest::class)->validateResolved();

        // 認証試行（仕様書 FN006）
        if (!Auth::guard('user')->attempt($request->only('email', 'password'))) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ]);
        }

        // メール未認証（仕様書 FN011）
        if (! Auth::guard('user')->user()->hasVerifiedEmail()) {
            Auth::guard('user')->logout();

            return redirect('/email/verify');
        }

        $request->session()->regenerate();
        return redirect()->intended(config('fortify.home'));
    }
}
