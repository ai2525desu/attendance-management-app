<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionController extends Controller
{
    // 一般ユーザーの申請一覧画面表示
    public function indexCorrection(Request $request)
    {
        // tab は動的セグメント＝画面のリロードで切り替えするため記述注意
        $tab = $request->query('tab', 'pending');
        $user = Auth::user();
        $attendanceCorrection = AttendanceCorrectRequest::with('attendanceBreakCorrects');
        return view('user.stamp_correction_request.list', compact('tab', 'user',));
    }

    // 管理者の申請一覧画面表示
    public function indexAdminCorrection()
    {
        return view('admin.stamp_correction_request.list');
    }
}
