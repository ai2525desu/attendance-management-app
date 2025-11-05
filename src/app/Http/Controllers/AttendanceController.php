<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // 一般ユーザーの出勤登録画面
    public function create()
    {
        return view('user.attendance.registration');
    }

    // 一般ユーザーの勤怠一覧画面
    public function index()
    {
        return view('user.attendance.list');
    }

    // 管理者の勤怠一覧画面表示
    public function indexAdmin()
    {
        return view('admin.attendance.list');
    }
}
