<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class GetDetailedInformationTest extends UserTestCase
{
    protected User $user;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->loginUser();
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $this->attendance = Attendance::factory()->worked()->for($this->user)->create();
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

    // 名前がログインユーザーの氏名になっているか
    public function test_to_check_if_the_name_is_the_logged_in_user_full_name()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('名前');
        $response->assertSee($this->user->name);
    }

    // 日付が選択された日付かどうか
    public function test_if_date_is_selected_date()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('日付');
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('m月d日'));
    }

    // 出勤と退勤が打刻した時間と一致しているか
    public function test_to_confirm_that_clock_in_and_clock_out_times_match_the_stamp_times()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('出勤・退勤');
        $response->assertSee($this->attendance->clock_in->format('H:i'));
        $response->assertSee($this->attendance->clock_out->format('H:i'));
    }

    // 休憩が打刻した時間と一致しているか
    public function test_to_confirm_that_break_start_and_break_end_times_match_the_stamp_times()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $response->assertSee('休憩');
        $attendanceBreak = $this->attendance->attendanceBreaks->first();
        $response->assertSee($attendanceBreak->break_start->format('H:i'));
        $response->assertSee($attendanceBreak->break_end->format('H:i'));
    }
}
