<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('adminpassword'),
        ];
    }

    // Seederと同じ想定で固定管理者を設定
    public function fixed()
    {
        return $this->state([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
        ]);
    }
}
