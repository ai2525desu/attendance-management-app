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

        $dateTime = Carbon::now();
        $response->assertSee($dateTime->isoFormat('Y年M月D日(ddd)'));
        $response->assertSee($dateTime->format('H:i'));

        Carbon::setTestNow();
    }
}
