<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\AdminTestCase;

class GetListInformationTest extends AdminTestCase
{
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->loginAdmin();

        // 一般ユーザー2名の勤怠登録
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $users = User::factory()->count(2)->create();
        $yesterday = Carbon::yesterday();
        $today     = Carbon::today();
        $tomorrow  = Carbon::tomorrow();
        $dates = [$yesterday, $today, $tomorrow];

        foreach ($users as $user) {
            foreach ($dates as $date) {
                $attendance = Attendance::factory()->worked($date)->for($user)->create();

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
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // その日の全ユーザーの勤怠情報が正確に確認できる
    public function 
}
