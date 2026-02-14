<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use App\Http\Requests\User\LoginRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // プロフィール更新・パスワード更新・リセット
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // ログイン試行のレート制限
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // 一般ユーザー専用 guard
        config(['fortify.guard' => 'user']);

        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.user.register');
        });

        // 登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // 登録後の遷移先（仕様書 FN005）
        Fortify::redirects('register', '/clock');

        // ログイン画面
        Fortify::loginView(function () {
            return view('auth.user.login');
        });

        // ログイン処理を LoginRequest に差し替え（仕様書 FN006〜FN009） ★★★
        app()->bind(
            AuthenticatedSessionController::class,
            function () {
                return new class extends AuthenticatedSessionController {
                    public function store(LoginRequest $request)
                    {
                        // 認証試行
                        if (! auth()->guard('user')->attempt($request->only('email', 'password'))) {
                            return back()
                                ->withInput()
                                ->with('status', 'login-error'); // 仕様書 FN009
                        }

                        // メール認証チェック（仕様書 FN011）
                        if (! auth()->user()->hasVerifiedEmail()) {
                            auth()->logout();
                            return redirect('/email/verify');
                        }

                        $request->session()->regenerate();
                        return redirect()->intended(config('fortify.home'));
                    }
                };
            }
        );

        // メール認証誘導画面（仕様書 FN011） ★★★
        Fortify::verifyEmailView(function () {
            return view('auth.user.verify-email');
        });

        // 認証メール再送（仕様書 FN012） ★★★
        // Fortify の verification.send が自動で動くので追加コード不要
    }
}
