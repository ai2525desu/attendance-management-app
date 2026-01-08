<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class AttendanceFunctionTest extends UserTestCase
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

    // 出勤ボタンが正しく機能しているか
    public function test_whether_the_attend_button_is_working_properly()
    {
        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        $response = $this->post(route('registration.clock_in'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_in' => $this->now->toDateTimeString(),
        ]);
        $response = $this->get(route('user.attendance.registration'));
        $response->assertSee('出勤中');
    }

    // 出勤は１日１回しかできないことを確認する
    public function test_that_you_can_only_go_to_work_once_a_day()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_in' => $this->now->toDateTimeString(),
            'clock_out' => Carbon::parse($this->now)->addHour(8)->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
    }

    // 出勤時刻が勤怠一覧画面で確認できる
    public function test_to_check_attendance_time_on_the_attendance_list_screen()
    {
        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        $response = $this->post(route('registration.clock_in'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_in' => $this->now->toDateTimeString(),
        ]);


        $response = $this->get(route('user.attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($this->now->isoFormat('MM/DD(ddd)'));
        $response->assertSee($this->now->format('H:i'));
    }
}
