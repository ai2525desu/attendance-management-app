<?php

namespace Tests\Feature\User\Attendance;

use Carbon\Carbon;
use Tests\UserTestCase;

class DateAndTimeTest extends UserTestCase
{
    public function test_whether_the_current_time_is_displayed_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $this->loginUser();

        $response = $this->get(route('user.attendance.registration'));
        $response->assertStatus(200);

        $expectedDate = Carbon::now()->isoFormat('Y年M月D日(ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);

        Carbon::setTestNow();
    }
}
