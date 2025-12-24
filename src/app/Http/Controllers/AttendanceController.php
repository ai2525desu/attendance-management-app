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
use Illuminate\Support\Facades\DB;

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

        $attendanceBreak = $hasTodayAttendance->attendanceBreaks()->whereNull('break_end')->latest('break_start')->first();
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

        // 修正前の勤怠データ
        $attendance = Attendance::with('attendanceBreaks')->findOrFail($id);
        // 申請中の修正データ
        $attendanceRequests = AttendanceCorrectRequest::with('attendanceBreakCorrects')->where('attendance_id', $id)->where('status', 'pending')->where('edited_by_admin', false)->first();

        // 修正申請があるかどうかのフラグ
        $applyingFixes = $attendanceRequests ? true : false;

        $amendmentApplication = $attendanceRequests;

        $display['breaks'] = [];

        // 申請前の情報を表示
        if (!$applyingFixes) {

            $display['clock_in'] = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null;
            $display['clock_out'] = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null;

            if (!$attendance->attendanceBreaks) {
                $attendance->attendanceBreaks = [];
            }

            foreach ($attendance->attendanceBreaks as $key => $break) {
                $start = $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : null;
                $end = $break->break_end ? Carbon::parse($break->break_end)->format('H:i') : null;

                $display['breaks'][$key] = [
                    'start' => $start,
                    'end'   => $end,
                ];
            }
            // 申請後の情報を表示
        } else {

            $display['clock_in'] = $attendanceRequests->correct_clock_in ? Carbon::parse($attendanceRequests->correct_clock_in)->format('H:i') : null;
            $display['clock_out'] = $attendanceRequests->correct_clock_out ? Carbon::parse($attendanceRequests->correct_clock_out)->format('H:i') : null;

            if (!$attendanceRequests->attendanceBreakCorrects) {
                $attendanceRequests->attendanceBreakCorrects = [];
            }

            foreach ($attendanceRequests->attendanceBreakCorrects as $key => $break) {
                $start = $break->correct_break_start ? Carbon::parse($break->correct_break_start)->format('H:i') : null;
                $end = $break->correct_break_end ? Carbon::parse($break->correct_break_end)->format('H:i') : null;

                $display['breaks'][$key] = [
                    'start' => $start,
                    'end'   => $end,
                ];
            }
        }

        $display['newIndex'] = count($display['breaks']);
        $new = $display['newIndex'];

        $errors = session('errors');
        $clockInError = $errors?->first('correct_clock_in');
        $clockOutError = $errors?->first('correct_clock_out');
        if ($clockInError === $clockOutError) {
            $clockOutError = null;
        }

        return view('user.attendance.detail', compact('user', 'attendance', 'amendmentApplication', 'applyingFixes', 'display', 'new', 'clockInError', 'clockOutError'));
    }

    // 一般ユーザーの修正申請
    public function storeCorrection(AttendanceCorrectionFormRequest $request)
    {
        DB::transaction(function () use ($request) {

            $user = Auth::user();
            $attendance = Attendance::with('attendanceBreaks')->findOrFail($request->attendance_id);

            $exists = AttendanceCorrectRequest::with('attendanceBreakCorrects')->where('attendance_id', $attendance->id)->where('status', 'pending')->where('edited_by_admin', false)->first();
            if ($exists) {
                throw new \RuntimeException('すでに修正されています。');
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
                'edited_by_admin' => false,
            ]);

            // 休憩のデータをCarbonへの返還後に保存
            $breakStarts = $request->correct_break_start ?? [];
            $breakEnds = $request->correct_break_end ?? [];

            foreach ($breakStarts as $key => $break) {
                $startTime = $break['start'] ?? null;
                $endTime = $breakEnds[$key]['end'] ?? null;

                $start = $startTime ? Carbon::parse("$workDate $startTime")->format('Y-m-d H:i:s') : null;
                $end = $endTime ? Carbon::parse("$workDate $endTime")->format('Y-m-d H:i:s') : null;

                if (count($attendance->attendanceBreaks) <= $key) {
                    // 新しい休憩を追加した処理
                    if (!$startTime && !$endTime) {
                        continue;
                    }
                    AttendanceBreakCorrect::create([
                        'attendance_correct_request_id' => $attendanceCorrection->id,
                        'attendance_break_id' => null,
                        'correct_break_start' => $start,
                        'correct_break_end' => $end,
                    ]);
                } elseif (!$startTime && !$endTime) {
                    // 休憩を削除した処理
                    $attendanceBreakId = is_numeric($key) ? $attendance->attendanceBreaks[$key]->id : null;

                    AttendanceBreakCorrect::create([
                        'attendance_correct_request_id' => $attendanceCorrection->id,
                        'attendance_break_id' => $attendanceBreakId,
                        'correct_break_start' => null,
                        'correct_break_end' => null,
                    ]);
                } else {
                    // 既存の休憩を修正した処理
                    $attendanceBreakId = is_numeric($key) ? $attendance->attendanceBreaks[$key]->id : null;

                    AttendanceBreakCorrect::create([
                        'attendance_correct_request_id' => $attendanceCorrection->id,
                        'attendance_break_id' => $attendanceBreakId,
                        'correct_break_start' => $start,
                        'correct_break_end' => $end,
                    ]);
                }
            }
        });

        return redirect()->route('stamp_correction_request.list')->with('success', '勤怠の修正を申請しました。');
    }

    // 管理者の勤怠一覧画面表示
    public function indexAdmin(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $targetDate = Carbon::parse($date);
        $previous = $targetDate->copy()->subDay()->format('Y-m-d');
        $next = $targetDate->copy()->addDay()->format('Y-m-d');
        $attendances = Attendance::with('user', 'attendancebreaks')->whereDate('work_date', $targetDate)->get();
        return view('admin.attendance.list', compact('targetDate', 'previous', 'next', 'attendances'));
    }

    // 管理者の勤怠詳細画面表示
    public function editAdminDetail($id)
    {
        // 修正前の勤怠データ
        $attendance = Attendance::with('user', 'attendanceBreaks')->findOrFail($id);
        // 一般ユーザーが申請中の修正データ
        $attendanceRequests = AttendanceCorrectRequest::with('user', 'attendanceBreakCorrects')->where('attendance_id', $id)->where('status', 'pending')->where('edited_by_admin', false)->first();

        // 修正申請があるかどうかのフラグ
        $applyingFixes = $attendanceRequests ? true : false;

        $amendmentApplication = $attendanceRequests;

        $display['breaks'] = [];

        // 申請前の情報を表示
        if (!$applyingFixes) {

            $display['clock_in'] = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null;
            $display['clock_out'] = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null;

            if (!$attendance->attendanceBreaks) {
                $attendance->attendanceBreaks = [];
            }

            foreach ($attendance->attendanceBreaks as $key => $break) {
                $start = $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : null;
                $end = $break->break_end ? Carbon::parse($break->break_end)->format('H:i') : null;

                $display['breaks'][$key] = [
                    'start' => $start,
                    'end'   => $end,
                ];
            }
            // 申請後の情報を表示
        } else {

            $display['clock_in'] = $attendanceRequests->correct_clock_in ? Carbon::parse($attendanceRequests->correct_clock_in)->format('H:i') : null;
            $display['clock_out'] = $attendanceRequests->correct_clock_out ? Carbon::parse($attendanceRequests->correct_clock_out)->format('H:i') : null;

            if (!$attendanceRequests->attendanceBreakCorrects) {
                $attendanceRequests->attendanceBreakCorrects = [];
            }

            foreach ($attendanceRequests->attendanceBreakCorrects as $key => $break) {
                $start = $break->correct_break_start ? Carbon::parse($break->correct_break_start)->format('H:i') : null;
                $end = $break->correct_break_end ? Carbon::parse($break->correct_break_end)->format('H:i') : null;

                $display['breaks'][$key] = [
                    'start' => $start,
                    'end'   => $end,
                ];
            }
        }

        $display['newIndex'] = count($display['breaks']);
        $new = $display['newIndex'];

        $errors = session('errors');
        $clockInError = $errors?->first('correct_clock_in');
        $clockOutError = $errors?->first('correct_clock_out');
        if ($clockInError === $clockOutError) {
            $clockOutError = null;
        }

        return view('admin.attendance.detail', compact('attendance', 'amendmentApplication', 'applyingFixes', 'display', 'new', 'clockInError', 'clockOutError'));
    }

    // 管理者の修正機能
    public function storeAdminCorrection(AttendanceCorrectionFormRequest $request)
    {
        DB::transaction(function () use ($request) {

            $attendance = Attendance::with('user', 'attendanceBreaks')->findOrFail($request->attendance_id);

            $exists = AttendanceCorrectRequest::with('user', 'attendanceBreakCorrects')->where('attendance_id', $attendance->id)->where('status', 'approved')->first();
            if ($exists) {
                throw new \RuntimeException('すでに修正されています。');
            }

            // 出退勤のデータをCarbonに変換後、保存
            $workDate = $request->work_date;
            $inputClockIn = $request->correct_clock_in;
            $inputClockOut = $request->correct_clock_out;

            $convertedClockIn = $inputClockIn ? Carbon::parse("$workDate $inputClockIn")->format('Y-m-d H:i:s') : null;
            $convertedClockOut = $inputClockOut ? Carbon::parse("$workDate $inputClockOut")->format('Y-m-d H:i:s') : null;

            AttendanceCorrectRequest::create([
                'user_id' => $attendance->user->id,
                'attendance_id' => $attendance->id,
                'request_date' => Carbon::now(),
                'correct_clock_in' => $convertedClockIn,
                'correct_clock_out' => $convertedClockOut,
                'remarks' => $request->remarks,
                'status' => 'approved',
                'edited_by_admin' => true,
            ]);

            $attendance->update([
                'clock_in' => $convertedClockIn,
                'clock_out' => $convertedClockOut,
            ]);

            // 休憩のデータをCarbonへの返還後に直接AttendanBreaksのデータを更新や新規休憩を追加
            $breakStarts = $request->correct_break_start ?? [];
            $breakEnds = $request->correct_break_end ?? [];

            foreach ($breakStarts as $key => $break) {
                $startTime = $break['start'] ?? null;
                $endTime = $breakEnds[$key]['end'] ?? null;

                $start = $startTime ? Carbon::parse("$workDate $startTime")->format('Y-m-d H:i:s') : null;
                $end = $endTime ? Carbon::parse("$workDate $endTime")->format('Y-m-d H:i:s') : null;

                if (count($attendance->attendanceBreaks) <= $key) {
                    // 新しい休憩を追加した処理
                    if (!$startTime && !$endTime) {
                        continue;
                    }
                    $attendance->attendanceBreaks()->create([
                        'break_start' => $start,
                        'break_end' => $end,
                    ]);
                } elseif (isset($attendance->attendanceBreaks[$key]) && !$startTime && !$endTime) {
                    // 休憩を削除した処理
                    $attendance->attendanceBreaks[$key]->delete();
                } else {
                    // 既存の休憩を修正した処理
                    $attendance->attendanceBreaks[$key]->update([
                        'break_start' => $start,
                        'break_end' => $end,
                    ]);
                }
            }
        });

        return redirect()->route('stamp_correction_request.list')->with('success', '勤怠の修正を申請しました。');
    }
}
