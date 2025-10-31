<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    // 管理者のログイン画面表示
    public function loginAdmin()
    {
        return view('admin.auth.login');
    }
}
