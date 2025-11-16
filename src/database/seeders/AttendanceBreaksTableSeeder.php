<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $breakCount = rand(1, 3);
            AttendanceBreak::factory()
                ->count($breakCount)
                ->for($attendance)
                ->state(function () use ($attendance) {
                    $breakStart = Carbon::parse($attendance->clock_in)->addHours(rand(2, 6))->addMinutes(rand(0, 59));
                    $breakEnd = $breakStart->copy()->addMinutes(rand(20, 60));
                    if ($breakEnd->gt(Carbon::parse($attendance->clock_out))) {
                        $breakEnd = Carbon::parse($attendance->clock_out)->copy()->subMinutes(rand(5, 10));
                    }
                    return [
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                    ];
                })
                ->create();
        }
    }
}
