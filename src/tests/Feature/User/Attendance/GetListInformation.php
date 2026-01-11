<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class GetListInformation extends UserTestCase
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
        $baseTime = Carbon::now();
        $clockOutTime = Carbon::parse($baseTime)->addHour(8);

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $baseTime->toDateString(),
            'clock_in' => $baseTime->toDateTimeString(),
            'clock_out' => $clockOutTime->toDateTimeString(),
        ]);

        $breakStart = Carbon::parse($baseTime)->addHour(4);
        $breakEnd = Carbon::parse($breakStart)->addHour(1);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->toDateTimeString(),
            'break_end' => $breakEnd->toDateTimeString(),
        ]);

        $response = $this->get(route('user.attendance.list'));
        $response->assertStatus(200);

        $response->assertSee($attendance->work_date->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
        // 勤怠一覧画面に、計算後の休憩時間（1時間）が表示されていることを確認
        $response->assertSee('01:00');
    }
}
