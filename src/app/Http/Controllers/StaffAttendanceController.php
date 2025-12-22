<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class StaffAttendanceController extends Controller
{
    // スタッフ一覧画面表示
    public function indexStaffList()
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.staff.list', compact('users'));
    }

    // 各スタッフ月次勤怠一覧画面
    public function indexStaffAttendanceList(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $targetDate = Carbon::create($year, $month, 1);
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $attendances = Attendance::with('user', 'attendancebreaks')
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
        return view('admin.staff.attendance_list', compact('user', 'daysInMonth', 'targetDate', 'previous', 'next'));
    }

    // 各スタッフの勤怠一覧画面でのCSV出力機能
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $targetDate = Carbon::create($year, $month, 1);
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $attendances = Attendance::with('user', 'attendancebreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d'),])
            ->get()
            ->keyBy(function ($workDate) {
                return $workDate->work_date->format('Y-m-d');
            });

        $csvData = [];
        $csvData[] = ['ユーザー名', $user->name];
        $csvData[] = ['月次勤怠', $targetDate->format('Y/m')];
        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計', '詳細',];

        for ($day = 1; $day <= $targetDate->daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $attendance = $attendances->get($date->format('Y-m-d'));

            $csvData[] = [
                $date->format('Y-m-d'),
                $attendance ? optional($attendance->clock_in)->format('H:i') : '',
                $attendance ? optional($attendance->clock_out)->format('H:i') : '',
                $attendance ? $attendance->displayBreakTimeInHourFormat() : '',
                $attendance ? $attendance->displayWorkingTimeInHourFormat() : '',
                $attendance ? route('admin.attendance.detail', ['id' => $attendance->id]) : '',
            ];
        }

        $filename = 'attendance_' . $user->id . $year . $month . '.csv';
        $handle = fopen('php://memory', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        return Response::stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
