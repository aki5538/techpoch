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
use Laravel\Fortify\Features;

use App\Http\Requests\User\LoginRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\UserLoginController;
use Illuminate\Support\Facades\Auth;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        config(['auth.defaults.guard' => 'user']);

        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        config(['fortify.guard' => 'user']);

        Fortify::registerView(fn() => view('auth.user.register'));

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::redirects('register', '/email/verify');

        Fortify::loginView(fn() => view('auth.user.login'));

        app()->bind(
            AuthenticatedSessionController::class,
            UserLoginController::class
        );

        Fortify::verifyEmailView(fn() => view('auth.user.verify-email'));
        Fortify::authenticateUsing(function ($request) {
            return Auth::guard('user')->getProvider()->retrieveByCredentials([
                'email' => $request->email,
            ]);
        });
    }
}
