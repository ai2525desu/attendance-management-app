<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class EndOfWorkTest extends UserTestCase
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

    // 退勤ボタンが正しく機能するか
    public function test_that_the_clock_out_button_is_working_properly()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();

        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('退勤');

        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(8));
        $clockOutTime = Carbon::now();
        $response = $this->post(route('registration.clock_out'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'clock_out' => $clockOutTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    // 退勤時刻が勤怠一覧画面で確認できるか
    public function test_to_check_clocking_out_time_on_the_attendance_list_screen()
    {
        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $baseTime = Carbon::now();
        $response = $this->post(route('registration.clock_in'));

        Carbon::setTestNow(Carbon::parse($baseTime)->addHour(8));
        $clockOutTime = Carbon::now();
        $response = $this->post(route('registration.clock_out'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
            'clock_out' => $clockOutTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($baseTime->isoFormat('MM/DD(ddd)'));
        $response->assertSee($clockOutTime->format('H:i'));
    }
}
