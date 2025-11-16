<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    // データを1件作成するためのメソッド
    public function definition()
    {
        $baseDate = Carbon::now();
        $lastDay = $baseDate->copy()->endOfMonth()->day;
        $workDate = $baseDate->copy()->setDay(rand(1, $lastDay));
        $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
        $clockOut = $clockIn->copy()->addHours(rand(7, 10))->addMinutes(rand(0, 59));

        return [
            // user_idの指定はSeeder側でfor($user)にて紐づけ
            'work_date' => $workDate->toDateString(),
            'clock_in' => $clockIn->toDateTimeString(),
            'clock_out' => $clockOut->toDateTimeString(),
        ];
    }

    // public function definition()
    // {
    //     $baseDate = Carbon::now();
    //     $lastDay = $baseDate->copy()->endOfMonth()->day;
    //     $workDate = $baseDate->copy()->setDay(rand(1, $lastDay));
    //     $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
    //     $clockOut = $clockIn->copy()->addHours(rand(7, 10))->addMinutes(rand(0, 59));

    //     return [
    //         // user_idの指定はSeeder側でfor($user)にて紐づけ
    //         'work_date' => $workDate->toDateString(),
    //         'clock_in' => $clockIn->toDateTimeString(),
    //         'clock_out' => $clockOut->toDateTimeString(),
    //     ];
    // }

    // 月内での重複を回避して、ダミーデータを作成するためのメソッド
    public function monthBased(Carbon $baseDate, int $index)
    {
        $lastDay = $baseDate->copy()->endOfMonth()->day;

        $day = ($index % $lastDay) + 1;
        $workDate = $baseDate->copy()->setDay(rand(1, $day));
        $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
        $clockOut = $clockIn->copy()->addHours(rand(7, 10))->addMinutes(rand(0, 59));

        return $this->state([
            'work_date' => $workDate->toDateString(),
            'clock_in' => $clockIn->toDateTimeString(),
            'clock_out' => $clockOut->toDateTimeString(),
        ]);
    }
}
