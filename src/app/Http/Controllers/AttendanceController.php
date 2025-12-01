<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionFormRequest;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceBreakCorrect;
use App\Models\AttendanceCorrectRequest;
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

        $attendances = Attendance::with('attendancebreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d'),])
            ->get()
            ->keyBy(function ($workDate) {
                return $workDate->work_date->format('Y-m-d');
            });

        $daysInMonth = [];
        for ($day = 1; $day <= $targetDate->daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $attendance = $attendances->get($date->format('Y-m-d'));
            $totalBreakFormat = $attendance ? $attendance->displayBreakTimeInHourFormat() : '';
            $totalWorkingTimeFormat = $attendance ? $attendance->displayWorkingTimeInHourFormat() : '';

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

    // 一般ユーザーの勤怠詳細画面
    public function editDetail($id)
    {
        $user = Auth::user();
        $attendance = Attendance::with('attendanceBreaks')->findOrFail($id);

        $amendmentApplication = AttendanceCorrectRequest::with('attendanceBreakCorrects')->where('attendance_id', $attendance->id)->where('status', 'pending')->first();
        $applyingFixes = $amendmentApplication ? true : false;

        $breakIndex = $attendance->attendanceBreaks->count();

        return view('user.attendance.detail', compact('user', 'attendance', 'applyingFixes', 'breakIndex'));
    }

    // 一般ユーザーの修正申請
    public function storeCorrection(AttendanceCorrectionFormRequest $request)
    {
        $user = Auth::user();
        $attendance = Attendance::with('attendanceBreaks')->findOrFail($request->attendance_id);

        $exists = AttendanceCorrectRequest::with('attendanceBreakCorrects')->where('attendance_id', $attendance->id)->where('status', 'pending')->first();
        if ($exists) {
            return back()->with('message', 'すでに修正申請されています。');
        }

        // 出退勤のデータをCarbonに変換後、保存
        $workDate = $request->work_date;
        $inputClockIn = $request->correct_clock_in;
        $inputClockOut = $request->correct_clock_out;

        $convertedClockIn = $inputClockIn ? Carbon::parse("$workDate $inputClockIn")->format('Y-m-d H:i:s') : null;
        $convertedClockOut = $inputClockOut ? Carbon::parse("$workDate $inputClockOut")->format('Y-m-d H:i:s') : null;

        $attendanceCorrection = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => Carbon::now(),
            'correct_clock_in' => $convertedClockIn,
            'correct_clock_out' => $convertedClockOut,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        // 休憩のデータをCarbonへの返還後に保存
        $breakStarts = $request->correct_break_start ?? [];
        $breakEnds = $request->correct_break_end ?? [];

        foreach ($breakStarts as $index => $allBreakstart) {
            $startTime = $allBreakstart['start'] ?? null;
            $endTime = $breakEnds[$index]['end'] ?? null;

            if (!$startTime && !$endTime) {
                continue;
            }

            $start = Carbon::parse("$workDate $startTime")->format('Y-m-d H:i:s');
            $end = Carbon::parse("$workDate $endTime")->format('Y-m-d H:i:s');

            AttendanceBreakCorrect::create([
                'attendance_correct_request_id' => $attendanceCorrection->id,
                'correct_break_start' => $start,
                'correct_break_end' => $end,
            ]);
        }

        return redirect()->route('user.stamp_correction_request.list')->with('success', '勤怠の修正を申請しました。');
    }

    // 管理者の勤怠一覧画面表示
    public function indexAdmin()
    {
        return view('admin.attendance.list');
    }
}
