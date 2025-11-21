<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Break_;

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
                $status = '退勤済';
            } elseif ($todayAttendance->attendanceBreaks()->whereNull('break_end')->exists()) {
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

    // 出勤時間の登録処理
    public function clockIn()
    {
        $user = Auth::user();
        $dateTime = Carbon::now();
        $hasTodayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        if ($hasTodayAttendance) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', '本日の出勤時間はすでに記録されています。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $dateTime->toDateString(),
            'clock_in' => $dateTime->toDateTimeString(),
        ]);

        return redirect()->route('user.attendance.registration')->with('successMessage', '出勤時間を記録しました。');
    }

    // 退勤時間の登録処理
    public function clockOut()
    {
        $user = Auth::user();
        $dateTime = Carbon::now();

        $hasTodayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->whereNotNull('clock_in')
            ->first();

        if (!$hasTodayAttendance) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', '本日の出勤記録がありません。');
        }

        if ($hasTodayAttendance->clock_out) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', 'すでに退勤済みです。');
        }

        $hasTodayAttendance->update(['clock_out' => $dateTime->toDateTimeString()]);

        return redirect()->route('user.attendance.registration')->with('successMessage', '退勤時間を記録しました');
    }

    // 休憩開始時間の登録処理
    public function breakStart()
    {
        $user = Auth::user();
        $dateTime = Carbon::now();


        $hasTodayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$hasTodayAttendance) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', '本日の出勤記録がありません。');
        }

        $attendanceBreak = $hasTodayAttendance->attendanceBreaks()->whereNull('break_end')->latest('break_start')->first();
        if ($attendanceBreak) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', '休憩中です。休憩を終了してください。');
        }

        AttendanceBreak::create([
            'attendance_id' => $hasTodayAttendance->id,
            'break_start' => $dateTime->toDateTimeString(),
        ]);

        return redirect()->route('user.attendance.registration')->with('successMessage', '休憩を開始しました。');
    }

    // 休憩終了時間の登録処理
    public function breakEnd()
    {
        $user = Auth::user();
        $dateTime = Carbon::now();

        $hasTodayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$hasTodayAttendance) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', '本日の出勤記録がありません。');
        }

        $attendanceBreak = $hasTodayAttendance->attendanceBreaks()->latest('break_start')->first();
        if (!$attendanceBreak || $attendanceBreak->break_end) {
            return redirect()->route('user.attendance.registration')->with('errorMessage', '開始済みの休憩記録が見つかりません。');
        }

        $attendanceBreak->update([
            'break_end' => $dateTime->toDateTimeString()
        ]);
        return redirect()->route('user.attendance.registration')->with('successMessage', '休憩を終了しました。');
    }

    // 一般ユーザーの勤怠一覧画面
    public function index(Request $request)
    {
        $user = Auth::user();

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $targetDate = Carbon::create($year, $month, 1);
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $attendances = Attendance::with('attendancebreaks')->where('user_id', $user->id)->whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d'),])->get()->keyBy('work_date');

        $daysInMonth = [];
        for ($day = 1; $day <= $targetDate->daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $attendance = $attendances->get($date->format('Y-m-d'));
            $totalBreakFormat = $attendance ? $attendance->displayBreakTimeInHourFormat() : '00:00';
            $totalWorkingTimeFormat = $attendance ? $attendance->displayWorkingTimeInHourFormat() : '00:00';

            $daysInMonth[] = [
                'date' => $date,
                'attendance' => $attendance,
                'total_break_format' => $totalBreakFormat,
                'total_working_time_format' => $totalWorkingTimeFormat,
            ];
        }

        $previous = $targetDate->copy()->subMonth();
        $next = $targetDate->copy()->addMonth();
        return view('user.attendance.list', compact('daysInMonth', 'targetDate', 'previous', 'next'));
    }

    // 管理者の勤怠一覧画面表示
    public function indexAdmin()
    {
        return view('admin.attendance.list');
    }
}
