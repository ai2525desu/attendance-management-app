<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $breakStart = Carbon::now()->setTime(rand(11, 15), rand(0, 59));
        $breakEnd = $breakStart->copy()->addMinutes(rand(20, 60));
        return [
            // attendance_idはSeeder側で紐づけ
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }

    // FeaturTest用
    public function forAttendance(Attendance $attendance)
    {
        return $this->state(function () use ($attendance) {
            return [
                'attendance_id' => $attendance->id,
            ];
        });
    }
}
