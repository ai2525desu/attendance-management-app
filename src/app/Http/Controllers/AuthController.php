<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 一般ユーザーのログイン画面表示
    public function login()
    {
        return view('user.auth.login');
    }

    // 一般ユーザーのログイン処理
    public function authenticate(LoginRequest $request)
    {
        $credentialsUser = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentialsUser)) {
            $user = Auth::user();
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')->with('errorMessage', 'メール認証が完了していません。メールを確認してください。');
            }

            $request->session()->regenerate();
            return redirect()->route('user.attendance.registration');
        }
        return back()->with('errorMessage', 'ログイン情報が登録されていません');
    }

    // 一般ユーザーのログアウト処理
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('user.auth.login');
    }

    // 一般ユーザーの会員登録画面表示
    public function register()
    {
        return view('user.auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice');
    }

    // 管理者のログイン画面表示
    public function loginAdmin()
    {
        return view('admin.auth.login');
    }
    // 管理者のログイン処理
    public function authenticateAdmin(LoginRequest $request)
    {
        $credentialsAdmin = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentialsAdmin)) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.list');
        }
        return back()->with('errorMessage', 'ログイン情報が登録されていません');
    }

    // 管理者のログアウト処理
    public function logoutAdmin(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.auth.login');
    }
}
