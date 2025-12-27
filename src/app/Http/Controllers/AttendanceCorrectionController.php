<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionController extends Controller
{
    // 一般ユーザーと管理者共通の申請一覧画面表示
    public function indexCorrection(Request $request)
    {
        return match ($request->get('role')) {
            'admin' => $this->adminCorrectionList($request),
            'user' => $this->userCorrectionList($request),
        };
    }

    // 一般ユーザーの申請一覧画面表示
    private function userCorrectionList(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $user = Auth::user();

        $statusMap = [
            'pending' => 'pending',
            'approved' => 'approved',
        ];

        $status = $statusMap[$tab] ?? 'pending';
        $corrections = AttendanceCorrectRequest::with('attendance')->where('user_id', $user->id)->where('status', $status)->where('edited_by_admin', false)->orderBy('created_at', 'desc')->get();
        $corrections->each(function ($correction) {
            $correction->status_text = AttendanceCorrectRequest::STATUS[$correction->status];
        });

        return view('user.stamp_correction_request.list', compact('tab', 'user', 'corrections'));
    }

    // 管理者の申請一覧画面表示
    private function adminCorrectionList(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $statusMap = [
            'pending' => 'pending',
            'approved' => 'approved',
        ];

        $status = $statusMap[$tab] ?? 'pending';
        $corrections = AttendanceCorrectRequest::with('attendance')->where('status', $status)->where('edited_by_admin', false)->orderBy('created_at', 'asc')->get();
        $corrections->each(function ($correction) {
            $correction->status_text = AttendanceCorrectRequest::STATUS[$correction->status];
        });

        return view('admin.stamp_correction_request.list', compact('tab', 'corrections',));
    }

    // 管理者の修正申請承認画面
    public function showApproval($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceCorrectRequest::with('user', 'attendance', 'attendanceBreakCorrects')->where('id', $attendance_correct_request_id)->where('status', 'pending')->where('edited_by_admin', false)->first();

        $display['correct_clock_in'] = $attendanceRequest->correct_clock_in ? Carbon::parse($attendanceRequest->correct_clock_in)->format('H:i') : null;
        $display['correct_clock_out'] = $attendanceRequest->correct_clock_out ? Carbon::parse($attendanceRequest->correct_clock_out)->format('H:i') : null;

        $display['correct_breaks'] = [];
        foreach ($attendanceRequest->attendanceBreakCorrects as $key => $break) {
            $start = $break->correct_break_start ? Carbon::parse($break->correct_break_start)->format('H:i') : null;
            $end = $break->correct_break_end ? Carbon::parse($break->correct_break_end)->format('H:i') : null;

            $display['correct_breaks'][$key] = [
                'start' => $start,
                'end'   => $end,
            ];
        }

        $display['newIndex'] = count($display['correct_breaks']);
        $new = $display['newIndex'];

        return view('admin.stamp_correction_request.approval', compact('attendanceRequest', 'display', 'new'));
    }
}
