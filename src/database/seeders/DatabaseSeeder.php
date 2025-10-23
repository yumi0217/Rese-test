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
        $this->call([
            AdminUserSeeder::class,
            AreasTableSeeder::class,
            GenresTableSeeder::class,
            RestaurantsTableSeeder::class,
            UsersTableSeeder::class,          // 先にオーナーを作る
            RestaurantOwnerSeeder::class,     // 最後に割り当て
        ]);
    }
}
