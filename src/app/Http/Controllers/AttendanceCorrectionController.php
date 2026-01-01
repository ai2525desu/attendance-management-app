<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // $attendanceRequest = AttendanceCorrectRequest::with('user', 'attendance', 'attendanceBreakCorrects')->where('id', $attendance_correct_request_id)->where('status', 'pending')->where('edited_by_admin', false)->first();
        $attendanceRequest = AttendanceCorrectRequest::with('user', 'attendance', 'attendanceBreakCorrects')->where('id', $attendance_correct_request_id)->firstOrFail();

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

    // 承認機能
    public function storeApproval($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceCorrectRequest::with('user', 'attendance', 'attendanceBreakCorrects')->where('id', $attendance_correct_request_id)->where('status', 'pending')->where('edited_by_admin', false)->firstOrFail();
        if ($attendanceRequest->status !== 'pending') {
            abort(403);
        }
        DB::transaction(function () use ($attendanceRequest) {
            // 承認のデータ
            AttendanceApproval::create([
                'admin_id' => Auth::guard('admin')->id(),
                'attendance_correct_request_id' => $attendanceRequest->id,
                'approved_date' => Carbon::now(),
            ]);

            // 修正前の勤怠の更新
            $attendance = $attendanceRequest->attendance;
            $attendance->update([
                'clock_in' => $attendanceRequest->correct_clock_in,
                'clock_out' => $attendanceRequest->correct_clock_out,
            ]);

            // 修正前の休憩の更新
            foreach ($attendanceRequest->attendanceBreakCorrects as $breakCorrect) {
                if (is_null($breakCorrect->attendance_break_id)) {
                    if (is_null($breakCorrect->correct_break_start) && (is_null($breakCorrect->correct_break_end))) {
                        continue;
                    }
                    $attendance->attendanceBreaks()->create([
                        'break_start' => $breakCorrect->correct_break_start,
                        'break_end' => $breakCorrect->correct_break_end,
                    ]);
                } elseif (is_null($breakCorrect->correct_break_start) && (is_null($breakCorrect->correct_break_end))) {
                    AttendanceBreak::where('id', $breakCorrect->attendance_break_id)->delete();
                } else {
                    AttendanceBreak::where('id', $breakCorrect->attendance_break_id)->update([
                        'break_start' => $breakCorrect->correct_break_start,
                        'break_end' => $breakCorrect->correct_break_end,
                    ]);
                }
            }

            // 修正申請テーブルのstatusを更新
            $attendanceRequest->update([
                'status' => 'approved',
            ]);
        });
        return redirect()->route('stamp_correction_request.list', ['tab' => 'approved'])->with('message', '承認しました');
    }
}
