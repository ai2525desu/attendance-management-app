<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 一般ユーザーのログイン画面表示
    public function login()
    {
        return view('user.auth.login');
    }

    public function authenticate(LoginRequest $request)
    {
        $credentialsUser = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentialsUser)) {
            $request->session()->regenerate();
            return redirect()->route('user.attendance.registration');
        }
        return back()->with('errorMessage', 'ログイン情報が登録されていません');

        // メール認証実装後のログイン認証機能
        // if (Auth::guard('web')->attempt($credentialsUser)) {
        //     $user = Auth::user();
        //     if (!$user->hasVerifiedEmail()) {
        //         return redirect()->route('verification.notice')->with('errorMessage', 'メール認証が完了していません。メールを確認してください。');
        //     }

        //     $request->session()->regenerate();
        //     return redirect()->route('user.attendance.registration');
        // }
        // return back()->with('errorMessage', 'ログイン情報が登録されていません');
    }

    // 一般ユーザーの会員登録画面表示
    public function register()
    {
        return view('user.auth.register');
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
