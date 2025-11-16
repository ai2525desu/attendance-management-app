<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::whereIn('id', [1, 2])->get();
        $months = [
            'previousMonth' => now()->subMonth(),
            'currentMonth' => now(),
            'nextMonth' => now()->addMonth(),
        ];

        foreach ($users as $user) {
            foreach ($months as $monthName => $baseDate) {
                Attendance::factory()
                    ->count(20)
                    ->for($user)
                    ->state(function () use ($baseDate) {
                        $lastDay = $baseDate->copy()->endOfMonth()->day;
                        $workDate = $baseDate->copy()->setDay(rand(1, $lastDay));
                        $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
                        $clockOut = $clockIn->copy()->addHours(rand(7, 10))->addMinutes(rand(0, 59));
                        return [
                            'work_date' => $workDate->toDateString(),
                            'clock_in' => $clockIn->toDateTimeString(),
                            'clock_out' => $clockOut->toDateTimeString(),
                        ];
                    })
                    ->create();
            }
        }
    }
}
