<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    // 一般ユーザーの申請一覧画面表示
    public function indexCorrection()
    {
        return view('user.stamp_correction_request.list');
    }

    // 管理者の申請一覧画面表示
    public function indexAdminCorrection()
    {
        return view('admin.stamp_correction_request.list');
    }
}
