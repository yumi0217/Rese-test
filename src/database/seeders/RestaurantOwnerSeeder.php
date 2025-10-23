<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;

class RestaurantOwnerSeeder extends Seeder
{
    public function run(): void
    {
        // 例：オーナーメールと管轄店舗名の対応
        $map = [
            'test0@gmail.com' => ['仙人', '牛助'],     // test0 が 仙人 と 牛助 を担当
            'test1@gmail.com' => ['戦懐'],            // test1 が 戦懐 を担当
            'owner2@example.com' => ['ルーク'],       // …
            // 追加してOK
        ];

        foreach ($map as $email => $shopNames) {
            $owner = User::where('role', 'owner')->where('email', $email)->first();

            if (!$owner) {
                $this->command->warn("Owner not found: {$email}");
                continue;
            }

            foreach ((array)$shopNames as $name) {
                $shop = Restaurant::where('name', $name)->first();
                if (!$shop) {
                    $this->command->warn("Shop not found: {$name}");
                    continue;
                }

                $shop->owner_id = $owner->id;
                $shop->save();

                $this->command->info("Assigned {$name} -> {$email}");
            }
        }
    }
}
