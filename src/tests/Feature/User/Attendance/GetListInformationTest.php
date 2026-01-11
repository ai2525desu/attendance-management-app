<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

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
}
