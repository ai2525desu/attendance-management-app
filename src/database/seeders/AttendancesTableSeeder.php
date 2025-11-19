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
            now()->subMonth(),
            now(),
            now()->addMonth(),
        ];

        foreach ($users as $user) {
            foreach ($months as $month) {
                Attendance::factory()->for($user)->forMonth($month)->create();
            }
        }
    }
}
