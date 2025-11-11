<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // 一般ユーザーの出勤登録画面
    public function create()
    {
        $user = Auth::user();
        $dateTime = Carbon::now();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        $status = '勤務外';
        $attendanceButton = 'clock_in';
        $breakButton = null;

        if ($todayAttendance) {
            if ($todayAttendance->clock_out) {
                $status = '退勤済み';
            } elseif ($todayAttendance->breaks()->whereNull('break_end')->exists()) {
                $status = '休憩中';
                $attendanceButton = null;
                $breakButton = 'break_end';
            } else {
                $status = '出勤中';
                $attendanceButton = 'clock_out';
                $breakButton = 'break_start';
            }
        }

        return view('user.attendance.registration', compact('dateTime', 'status', 'attendanceButton', 'breakButton'));
    }

    // 勤怠登録機能:単一では動かないので分離させる必要あり
    // public function store(Request $request)
    // {
    //     $user = Auth::user();
    //     $dateTime = Carbon::now();

    //     Attendance::create([
    //         'user_id' => $user->id,
    //         'work_date' => $request->input('work_date'),
    //         'clock_in' => $request->input('clock_in'),
    //         'clock_out' => $request->input('clock_out'),
    //     ]);

    //     AttendanceBreak::create([
    //         'attendance_id' => $request->input($user->attendances->id),
    //         'break_start' => $request->input('break_start'),
    //         'break_end' => $request->input('break_end'),
    //     ]);

    //     return redirect();
    // }

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
