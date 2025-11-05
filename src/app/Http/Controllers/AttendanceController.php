<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // 一般ユーザー関連記述予定
    public function create()
    {
        return view('user.attendance.registration');
    }

    // 管理者の勤怠一覧画面表示
    public function indexAdmin()
    {
        return view('admin.attendance.list');
    }
}
