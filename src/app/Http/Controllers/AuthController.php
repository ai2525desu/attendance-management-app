<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    // 一般ユーザーのログイン画面
    public function login()
    {
        return view('user.auth.login');
    }

    // 一般ユーザーの会員登録画面
    public function register()
    {
        return view('user.auth.register');
    }

    // 管理者のログイン画面表示
    public function loginAdmin()
    {
        return view('admin.auth.login');
    }
}
