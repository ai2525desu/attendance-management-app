<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 上から順に読み込むので順序に注意
        $this->call(AdminsTableseeder::class);
    }
}
