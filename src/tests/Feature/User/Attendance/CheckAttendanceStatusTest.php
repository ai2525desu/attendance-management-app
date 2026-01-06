<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Tests\UserTestCase;

use function PHPUnit\Framework\assertNull;

class CheckAttendanceStatusTest extends UserTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 勤務外と表示されているか
    public function test_to_check_if_your_status_is_off_duty()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $this->loginUser();

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    // 出勤中と表示されているか
    public function test_to_check_if_your_status_is_at_work()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 7, 8, 0, 0));
        $dateTime = Carbon::now();
        $user = $this->loginUser();
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $dateTime->toDateString(),
            'clock_in' => $dateTime->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // 休憩中と表示されているどうか
    public function test_to_check_if_your_status_is_on_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 7, 8, 0, 0));
        $dateTime = Carbon::now();
        $user = $this->loginUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $dateTime->toDateString(),
            'clock_in' => $dateTime->toDateTimeString(),
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse($attendance->clock_in)->addHours(4)->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // 退勤済と表示されているかどうか
    // public function test_to_check_if_status_is_clocked_out() {}
}
