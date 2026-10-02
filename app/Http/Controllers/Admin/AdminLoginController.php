<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function create(): View
    {
        return view('admin.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {

        $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required' => 'メールアドレスを入力してください',
                'email.email' => 'メールアドレスはメール形式で入力してください',
                'password.required' => 'パスワードを入力してください',
            ]
        );

        $user = User::where('email', $request->email)
            ->where('admin_status', true)
            ->first();

        if (
            ! $user ||
            ! Hash::check($request->password, $user->password)
        ) {
            return back()
                ->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ])
                ->withInput();
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/admin/attendance/list');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
