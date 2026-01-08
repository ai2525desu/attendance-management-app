<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class AttendanceBreakFunctionTest extends UserTestCase
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

    // 休憩ボタンが正しく機能しているか
    public function test_if_the_attendance_break_button_works_properly()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(4));
        $breakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // 休憩が1日に何回もできるか
    public function test_whether_you_can_take_multiple_breaks_in_a_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        // 1回目の休憩入と休憩戻
        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(4));
        $firstBreakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));

        Carbon::setTestNow(Carbon::parse($firstBreakStart)->addHour(1));
        $firstBreakEnd = Carbon::now();
        $response = $this->post(route('registration.break_end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $firstBreakStart->toDateTimeString(),
            'break_end' => $firstBreakEnd->toDateTimeString(),
        ]);

        // 2回目の休憩入と休憩戻
        Carbon::setTestNow(Carbon::parse($firstBreakEnd)->addHour(2));
        $secondBreakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));

        Carbon::setTestNow(Carbon::parse($secondBreakStart)->addHour(1));
        $secondBreakEnd = Carbon::now();
        $response = $this->post(route('registration.break_end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $secondBreakStart->toDateTimeString(),
            'break_end' => $secondBreakEnd->toDateTimeString(),
        ]);
        $this->assertDatabaseCount('attendance_breaks', 2);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    // 休憩戻ボタンが正しく機能しているか
    public function test_if_the_end_break_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(4));
        $breakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::parse($breakStart)->addHour(1));
        $breakEnd = Carbon::now();
        $response = $this->post(route('registration.break_end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->toDateTimeString(),
            'break_end' => $breakEnd->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // 休憩戻は1日に何回でもできるか
    public function test_to_see_if_you_can_complete_multiple_breaks_in_a_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        // 1回目の休憩入と休憩戻
        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(4));
        $firstBreakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));

        Carbon::setTestNow(Carbon::parse($firstBreakStart)->addHour(1));
        $firstBreakEnd = Carbon::now();
        $response = $this->post(route('registration.break_end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $firstBreakStart->toDateTimeString(),
            'break_end' => $firstBreakEnd->toDateTimeString(),
        ]);

        // 2回目の休憩入
        Carbon::setTestNow(Carbon::parse($firstBreakEnd)->addHour(2));
        $secondBreakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $secondBreakStart->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻');
    }

    // 休憩時刻が勤怠一覧画面でも確認することができるか
    public function test_to_check_break_time_on_attendance_list_screen()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(4));
        $breakStart = Carbon::now();
        $response = $this->post(route('registration.break_start'));

        Carbon::setTestNow(Carbon::parse($breakStart)->addHour(1));
        $breakEnd = Carbon::now();
        $response = $this->post(route('registration.break_end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->toDateTimeString(),
            'break_end' => $breakEnd->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($attendance->work_date->isoFormat('MM/DD(ddd)'));
        // 勤怠一覧画面に、計算後の休憩時間（1時間）が表示されていることを確認
        $response->assertSee('01:00');
    }
}
