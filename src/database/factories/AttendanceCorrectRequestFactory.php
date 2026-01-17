<?php

namespace Database\Factories;

use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectRequestFactory extends Factory
{
    protected $model = AttendanceCorrectRequest::class;

    public function definition()
    {
        return [
            'user_id' => null,
            'attendance_id' => null,
            'request_date' => Carbon::now()->toDateString(),
            'correct_clock_in' => '9:00',
            'correct_clock_out' => null,
            'remarks' => '修正申請',
            'status' => 'pending',
            'edited_by_admin' => false,
        ];
    }

    public function approved()
    {
        return $this->state(function () {
            return [
                'status' => 'approved',
            ];
        });
    }
}
