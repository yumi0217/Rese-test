<?php

namespace Tests\Feature\User;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MyPageTest extends TestCase
{
    use RefreshDatabase;

    /** remember_token を触らず、メール認証済みユーザーを作成 */
    private function makeUser(string $role = 'user'): User
    {
        return User::forceCreate([
            'name'              => 'Tester',
            'email'             => uniqid($role . '_', true) . '@example.com',
            'password'          => Hash::make('password123'),
            // role カラムが無ければこの行は削除
            'role'              => $role,
            'email_verified_at' => now(),
        ]);
    }

    /** Area / Genre を直 insert（timestamps 非依存）→ 店舗を作成 */
    private function makeShop(string $name = '店舗'): Restaurant
    {
        $owner   = $this->makeUser('owner');
        $areaId  = (int) DB::table('areas')->insertGetId(['name' => '東京']);
        $genreId = (int) DB::table('genres')->insertGetId(['name' => '和食']);

        return Restaurant::forceCreate([
            'owner_id'    => $owner->id,
            'area_id'     => $areaId,
            'genre_id'    => $genreId,
            'name'        => $name,
            'description' => '説明',
            'image_url'   => null,
        ]);
    }

    /** 予約を必要最小限のカラムで作成 */
    private function makeReservation(User $user, Restaurant $shop): Reservation
    {
        return Reservation::forceCreate([
            'user_id'          => $user->id,
            'restaurant_id'    => $shop->id,
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '12:00',
            'number_of_people' => 2,
            'qr_token'         => 'tok_' . Str::random(32),
        ]);
    }

    /** @test */
    public function it_shows_my_reservations_and_favorites()
    {
        /** @var AuthenticatableContract|User $user */
        $user = $this->makeUser('user');
        $shop = $this->makeShop('マイページ店舗');
        $this->makeReservation($user, $shop);

        $this->actingAs($user, 'web')
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee($shop->name)
            ->assertSee('12:00');
    }
}
