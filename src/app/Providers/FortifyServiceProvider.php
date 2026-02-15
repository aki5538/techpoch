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

// ★ ここで use する（boot の外）
use App\Http\Controllers\Auth\UserLoginController;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // プロフィール更新・パスワード更新・リセット
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // レート制限
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        // 一般ユーザー専用 guard
        config(['fortify.guard' => 'user']);

        // 会員登録画面
        Fortify::registerView(fn() => view('auth.user.register'));

        Fortify::createUsersUsing(CreateNewUser::class);

        // 登録後の遷移先
        Fortify::redirects('register', '/clock');

        // ログイン画面
        Fortify::loginView(fn() => view('auth.user.login'));

        // ★★★ ここで Fortify のログイン処理を差し替える ★★★
        app()->bind(
            AuthenticatedSessionController::class,
            UserLoginController::class
        );

        // メール認証誘導画面
        Fortify::verifyEmailView(fn() => view('auth.user.verify-email'));
    }
}
