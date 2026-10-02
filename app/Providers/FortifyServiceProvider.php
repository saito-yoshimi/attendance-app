<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(
            UpdateUserProfileInformation::class
        );
        Fortify::updateUserPasswordsUsing(
            UpdateUserPassword::class
        );
        Fortify::resetUserPasswordsUsing(
            ResetUserPassword::class
        );

        Fortify::redirectUserForTwoFactorAuthenticationUsing(
            RedirectIfTwoFactorAuthenticatable::class
        );

        /**
         * ログイン認証
         */
        Fortify::authenticateUsing(function (Request $request) {

            Validator::make(
                $request->all(),
                [
                    'email' => [
                        'required',
                        'email',
                    ],

                    'password' => [
                        'required',
                    ],
                ],
                [
                    'email.required' => 'メールアドレスを入力してください',

                    'email.email' => 'メールアドレスはメール形式で入力してください',

                    'password.required' => 'パスワードを入力してください',
                ]
            )->validate();

            $user = User::where(
                'email',
                $request->email
            )->first();

            if (
                $user &&
                Hash::check(
                    $request->password,
                    $user->password
                )
            ) {
                return $user;
            }

            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        });

        /**
         * ログイン試行回数制限
         */
        RateLimiter::for(
            'login',
            function (Request $request) {

                $throttleKey =
                    Str::transliterate(
                        Str::lower(
                            $request->input(
                                Fortify::username()
                            )
                        ).'|'.$request->ip()
                    );

                return Limit::perMinute(5)
                    ->by($throttleKey);
            }
        );

        /**
         * ログイン画面
         */
        Fortify::loginView(function () {
            return view('user.user-login');
        });

        /**
         * 会員登録画面
         */
        Fortify::registerView(function () {
            return view('user.register');
        });

        /**
         * メール認証画面
         */
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });
    }
}
