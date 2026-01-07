<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class CheckAttendanceStatusTest extends UserTestCase
{
    protected User $user;
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $this->now = Carbon::now();
        $this->user = $this->loginUser();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 勤務外と表示されているか
    public function test_to_check_if_your_status_is_off_duty()
    {
        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    // 出勤中と表示されているか
    public function test_to_check_if_your_status_is_at_work()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_in' => $this->now->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // 休憩中と表示されているどうか
    public function test_to_check_if_your_status_is_on_break()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_in' => $this->now->toDateTimeString(),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse($attendance->clock_in)->addHours(4)->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // 退勤済と表示されているかどうか
    public function test_to_check_if_status_is_clocked_out()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_in' => $this->now->toDateTimeString(),
            'clock_out' => Carbon::parse($this->now)->addHour(8)->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
