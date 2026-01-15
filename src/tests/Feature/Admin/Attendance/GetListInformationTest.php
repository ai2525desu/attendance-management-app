<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\AdminTestCase;

class GetListInformationTest extends AdminTestCase
{
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->loginAdmin();

        // 一般ユーザー2名の勤怠登録
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $users = User::factory()->count(2)->create();
        $yesterday = Carbon::yesterday();
        $today     = Carbon::today();
        $tomorrow  = Carbon::tomorrow();
        $dates = [$yesterday, $today, $tomorrow];

        foreach ($users as $user) {
            foreach ($dates as $date) {
                $attendance = Attendance::factory()->worked($date)->for($user)->create();

                $clockInTime = Carbon::parse($attendance->clock_in);
                $breakStart = $clockInTime->copy()->addHour(4);
                $breakEnd = $breakStart->copy()->addHour(1);

                AttendanceBreak::factory()
                    ->forAttendance($attendance)
                    ->create([
                        'break_start' => $breakStart->toDateTimeString(),
                        'break_end' => $breakEnd->toDateTimeString(),
                    ]);
            }
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // その日の全ユーザーの勤怠情報が正確に確認できる
    public function test_to_accurately_check_the_attendance_of_all_users_on_a_given_day()
    {
        $response = $this->get(route('admin.attendance.list'));
        $response->assertStatus(200);

        $currentDay = Carbon::today();
        $response->assertSee($currentDay->format('Y/m/d'));

        $todayAttendances = Attendance::whereDate('work_date', $currentDay)->get();
        $this->assertCount(2, $todayAttendances);

        foreach ($todayAttendances as $attendance) {
            $response->assertSee($attendance->user->name);
            $response->assertSee($attendance->clock_in->format('H:i'));
            $response->assertSee($attendance->clock_out->format('H:i'));
            $response->assertSee($attendance->displayBreakTimeInHourFormat());
            $response->assertSee($attendance->displayWorkingTimeInHourFormat());
        }
    }

    // 遷移時に現在の日付が表示されているか
    public function test_to_confirm_that_the_current_date_is_displayed_when_transitioning()
    {
        $response = $this->get(route('admin.attendance.list'));
        $response->assertStatus(200);

        $workDate = Carbon::today();
        $response->assertSee($workDate->isoFormat('Y年M月D日'));
        $response->assertSee('の勤怠');
        $response->assertSee($workDate->format('Y/m/d'));
    }

    // 前日を押下した際に前の日付の勤怠情報が表示されているか
    public function test_to_confirm_that_the_previous_day_attendance_information_is_displayed_when_the_previous_day_button_is_pressed()
    {
        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('前日');

        $currentDay = Carbon::today();
        $previous = $currentDay->copy()->subDay()->format('Y-m-d');
        $response = $this->get(route('admin.attendance.list', ['date' => $previous]));

        $response->assertStatus(200);
        $response->assertSee(
            $currentDay->copy()->subDay()->isoFormat('Y年M月D日')
        );

        $previousAttendances = Attendance::whereDate('work_date', $previous)->get();
        $this->assertCount(2, $previousAttendances);

        foreach ($previousAttendances as $attendance) {
            $response->assertSee($attendance->user->name);
            $response->assertSee($attendance->clock_in->format('H:i'));
            $response->assertSee($attendance->clock_out->format('H:i'));
            $response->assertSee($attendance->displayBreakTimeInHourFormat());
            $response->assertSee($attendance->displayWorkingTimeInHourFormat());
        }
    }

    // 翌日を押下した際に次の日付の勤怠情報が表示されているか
    public function test_to_confirm_that_the_next_day_attendance_information_is_displayed_when_the_next_day_button_is_pressed()
    {
        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('翌日');

        $currentDay = Carbon::today();
        $next = $currentDay->copy()->addDay()->format('Y-m-d');
        $response = $this->get(route('admin.attendance.list', ['date' => $next]));

        $response->assertStatus(200);
        $response->assertSee(
            $currentDay->copy()->addDay()->isoFormat('Y年M月D日')
        );

        $nextAttendances = Attendance::whereDate('work_date', $next)->get();
        $this->assertCount(2, $nextAttendances);

        foreach ($nextAttendances as $attendance) {
            $response->assertSee($attendance->user->name);
            $response->assertSee($attendance->clock_in->format('H:i'));
            $response->assertSee($attendance->clock_out->format('H:i'));
            $response->assertSee($attendance->displayBreakTimeInHourFormat());
            $response->assertSee($attendance->displayWorkingTimeInHourFormat());
        }
    }
}
