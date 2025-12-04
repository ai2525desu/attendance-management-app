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
        $tab = $request->query('tab', 'pending');
        $user = Auth::user();

        $statusMap = [
            'pending' => 'pending',
            'approved' => 'approved',
        ];


        $status = $statusMap[$tab] ?? 'pending';
        $corrections = AttendanceCorrectRequest::with('attendance')->where('user_id', $user->id)->where('status', $status)->orderBy('created_at', 'desc')
            ->get();
        $corrections->each(function ($correction) {
            $correction->status_text = AttendanceCorrectRequest::STATUS[$correction->status];
        });

        return view('user.stamp_correction_request.list', compact('tab', 'user', 'corrections'));
    }

    // 管理者の申請一覧画面表示
    public function indexAdminCorrection()
    {
        return view('admin.stamp_correction_request.list');
    }
}
