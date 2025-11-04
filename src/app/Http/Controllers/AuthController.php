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
            return redirect()->intended('/admin/attendance/list');
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
