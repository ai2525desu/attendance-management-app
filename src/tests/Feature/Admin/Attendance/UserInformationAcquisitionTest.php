<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\AdminTestCase;

class UserInformationAcquisitionTest extends AdminTestCase
{
    protected Admin $admin;
    protected Collection $users;
    protected User $targetUser;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->loginAdmin();

        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $this->users = User::factory()->count(2)->create();

        // 勤怠一覧テストで扱うユーザーとそのユーザーの勤怠を作成
        $this->targetUser = $this->users->first();
        $this->attendance = Attendance::factory()->worked(Carbon::today())->for($this->targetUser)->create();
        $clockInTime = Carbon::parse($this->attendance->clock_in);
        $breakStart = $clockInTime->copy()->addHour(4);
        $breakEnd = $breakStart->copy()->addHour(1);
        AttendanceBreak::factory()
            ->forAttendance($this->attendance)
            ->create([
                'break_start' => $breakStart->toDateTimeString(),
                'break_end' => $breakEnd->toDateTimeString(),
            ]);
    }


    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // すべての一般ユーザーの氏名とメールアドレスを確認できるか
    public function test_to_check_the_names_and_email_addresses_of_all_regular_users()
    {
        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);
        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    // 選択したユーザーの勤怠情報が正しく表示されるか
    public function test_to_confirm_that_the_selected_user_attendance_information_is_displayed_correctly()
    {
        $response = $this->get(route('admin.staff.attendance_list', ['id' => $this->targetUser->id]));

        $response->assertStatus(200);

        $targetDate = Carbon::today();
        $response->assertSee($this->targetUser->name);
        $response->assertSee($targetDate->format('Y/m'));
        $response->assertSee($this->attendance->work_date->isoFormat('MM/DD(ddd)'));
        $response->assertSee($this->attendance->clock_in->format('H:i'));
        $response->assertSee($this->attendance->clock_out->format('H:i'));
        $response->assertSee($this->attendance->displayBreakTimeInHourFormat());
        $response->assertSee($this->attendance->displayWorkingTimeInHourFormat());
    }

    // 前月の勤怠データが表示されるか
    public function test_whether_the_previous_month_attendance_is_displayed()
    {
        $previousMonthData = Carbon::now()->subMonth();
        $previousAttendance = Attendance::factory()->worked($previousMonthData)->for($this->targetUser)->create();

        $clockInTime = Carbon::parse($previousAttendance->clock_in);
        $breakStart = $clockInTime->copy()->addHour(4);
        $breakEnd = $breakStart->copy()->addHour(1);
        AttendanceBreak::factory()
            ->forAttendance($previousAttendance)
            ->create([
                'break_start' => $breakStart->toDateTimeString(),
                'break_end' => $breakEnd->toDateTimeString(),
            ]);

        $response = $this->get(route('admin.staff.attendance_list', ['id' => $this->targetUser->id]));

        $response->assertStatus(200);
        $response->assertSee('前月');

        $response = $this->get(route('admin.staff.attendance_list', ['id' => $this->targetUser->id, 'year' => $previousMonthData->year, 'month' => $previousMonthData->month]));

        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $previousMonth = $previousMonthData->format('Y/m');
        $response->assertSee($previousMonth);
        $response->assertSee($previousAttendance->work_date->isoFormat('MM/DD(ddd)'));
        $response->assertSee($previousAttendance->clock_in->format('H:i'));
        $response->assertSee($previousAttendance->clock_out->format('H:i'));
        $response->assertSee($previousAttendance->displayBreakTimeInHourFormat());
        $response->assertSee($previousAttendance->displayWorkingTimeInHourFormat());
    }

    // 翌月の勤怠データが表示されるか
    public function test_whether_the_next_month_attendance_is_displayed()
    {
        $nextMonthData = Carbon::now()->addMonth();
        $nextAttendance = Attendance::factory()->worked($nextMonthData)->for($this->targetUser)->create();

        $clockInTime = Carbon::parse($nextAttendance->clock_in);
        $breakStart = $clockInTime->copy()->addHour(4);
        $breakEnd = $breakStart->copy()->addHour(1);
        AttendanceBreak::factory()
            ->forAttendance($nextAttendance)
            ->create([
                'break_start' => $breakStart->toDateTimeString(),
                'break_end' => $breakEnd->toDateTimeString(),
            ]);

        $response = $this->get(route('admin.staff.attendance_list', ['id' => $this->targetUser->id]));

        $response->assertStatus(200);
        $response->assertSee('翌月');

        $response = $this->get(route('admin.staff.attendance_list', ['id' => $this->targetUser->id, 'year' => $nextMonthData->year, 'month' => $nextMonthData->month]));

        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $nextMonth = $nextMonthData->format('Y/m');
        $response->assertSee($nextMonth);
        $response->assertSee($nextAttendance->work_date->isoFormat('MM/DD(ddd)'));
        $response->assertSee($nextAttendance->clock_in->format('H:i'));
        $response->assertSee($nextAttendance->clock_out->format('H:i'));
        $response->assertSee($nextAttendance->displayBreakTimeInHourFormat());
        $response->assertSee($nextAttendance->displayWorkingTimeInHourFormat());
    }

    // 詳細ボタンを押すと勤怠詳細画面に遷移する
    public function test_that_transitions_to_the_attendance_details_screen_when_you_press_the_details_button()
    {
        $response = $this->get(route('admin.staff.attendance_list', ['id' => $this->targetUser->id]));

        $response->assertStatus(200);
        $response->assertSee('詳細');

        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('m月d日'));
        $response->assertSee($this->attendance->clock_in->format('H:i'));
        $response->assertSee($this->attendance->clock_out->format('H:i'));

        $attendanceBreak = $this->attendance->attendanceBreaks->first();
        $response->assertSee($attendanceBreak->break_start->format('H:i'));
        $response->assertSee($attendanceBreak->break_end->format('H:i'));
    }
}
