<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // forMonth()にてafterCreatingを使用してデータを生成できるようにするための記述
        return [
            'work_date' => Carbon::parse('2000-01-01'),
            'clock_in' => null,
            'clock_out' => null,
        ];
    }

    // 隔月ごとの勤怠データをまとめて生成するメソッド
    public function forMonth(Carbon $month)
    {
        return $this->afterCreating(function ($model) use ($month) {
            $lastDay = $month->copy()->endOfMonth()->day;

            $days = range(1, $lastDay);
            shuffle($days);
            $workDays = array_slice($days, 0, min(20, $lastDay));
            $usedDays = [];

            foreach ($workDays as $day) {
                if (in_array($day, $usedDays)) continue;
                $usedDays[] = $day;

                $workDate = $month->copy()->setDay($day);
                $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
                $clockOut = $clockIn->copy()->addHours(rand(7, 10))->addMinutes(rand(0, 59));

                Attendance::create([
                    'user_id' => $model->user_id,
                    'work_date' => $workDate->toDateString(),
                    'clock_in' => $clockIn->toDateTimeString(),
                    'clock_out' => $clockOut->toDateTimeString(),
                ]);
            }
            $model->delete();
        });
    }
}
