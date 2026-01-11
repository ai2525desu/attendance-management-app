<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

use function PHPUnit\Framework\assertDoesNotMatchRegularExpression;

class GetListInformationTest extends UserTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->loginUser();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 勤怠情報が全て表示されているか
    public function test_user_can_see_only_his_own_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $yesterday = Carbon::now()->subDay();
        $today = Carbon::now();
        $tomorrow = Carbon::now()->addDay();
        $attendances = [];

        $attendances[] = Attendance::factory()->worked($yesterday)->for($this->user)->create();
        $attendances[] = Attendance::factory()->worked($today)->for($this->user)->create();
        $attendances[] = Attendance::factory()->worked($tomorrow)->for($this->user)->create();

        foreach ($attendances as $attendance) {
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

        $response = $this->get(route('user.attendance.list'));
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date->isoFormat('MM/DD(ddd)'));
            $response->assertSee($attendance->clock_in->format('H:i'));
            $response->assertSee($attendance->clock_out->format('H:i'));
        }
        foreach ($attendances as $attendance) {
            $response->assertSee('01:00');
        }
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示されているか
    public function test_whether_the_current_month_is_displayed_when_transitioning_to_the_attendance_list_screen()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $currentMonth = Carbon::now()->format('Y/m');

        $response = $this->get(route('user.attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($currentMonth);
    }

    // 前月の勤怠データが表示されるか
    public function test_whether_the_previous_month_attendance_is_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $previousMonthData = Carbon::now()->subMonth();
        $previousAttendance = Attendance::factory()->worked($previousMonthData)->for($this->user)->create();

        $clockInTime = Carbon::parse($previousAttendance->clock_in);
        $breakStart = $clockInTime->copy()->addHour(4);
        $breakEnd = $breakStart->copy()->addHour(1);
        AttendanceBreak::factory()
            ->forAttendance($previousAttendance)
            ->create([
                'break_start' => $breakStart->toDateTimeString(),
                'break_end' => $breakEnd->toDateTimeString(),
            ]);

        $response = $this->get(route('user.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('前月');

        $response = $this->get('/attendance/list?year=2025&month=12');

        $response->assertStatus(200);
        $previousMonth = $previousMonthData->format('Y/m');
        $response->assertSee($previousMonth);
        $response->assertSee($previousAttendance->work_date->isoFormat('MM/DD(ddd)'));
        $response->assertSee($previousAttendance->clock_in->format('H:i'));
        $response->assertSee($previousAttendance->clock_out->format('H:i'));
        $response->assertSee('01:00');
    }

    // 翌月の勤怠データが表示されているか
    public function test_whether_the_next_month_attendance_is_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $nextMonthData = Carbon::now()->addMonth();
        $nextAttendance = Attendance::factory()->worked($nextMonthData)->for($this->user)->create();

        $clockInTime = Carbon::parse($nextAttendance->clock_in);
        $breakStart = $clockInTime->copy()->addHour(4);
        $breakEnd = $breakStart->copy()->addHour(1);
        AttendanceBreak::factory()
            ->forAttendance($nextAttendance)
            ->create([
                'break_start' => $breakStart->toDateTimeString(),
                'break_end' => $breakEnd->toDateTimeString(),
            ]);

        $response = $this->get(route('user.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('翌月');

        $response = $this->get('/attendance/list?year=2026&month=2');

        $response->assertStatus(200);
        $nextMonth = $nextMonthData->format('Y/m');
        $response->assertSee($nextMonth);
        $response->assertSee($nextAttendance->work_date->isoFormat('MM/DD(ddd)'));
        $response->assertSee($nextAttendance->clock_in->format('H:i'));
        $response->assertSee($nextAttendance->clock_out->format('H:i'));
        $response->assertSee('01:00');
    }
}
