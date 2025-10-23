<?php

namespace Tests\Feature\Shop;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationAndReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'user', ?string $email = null, string $name = '利用者'): User
    {
        return User::forceCreate([
            'name'              => $name,
            'email'             => $email ?? uniqid($role . '_', true) . '@example.com',
            'password'          => Hash::make('password123'),
            'role'              => $role, // 無ければ削除
            'email_verified_at' => now(),
        ]);
    }

    private function makeShop(?User $owner = null, string $name = '店舗'): Restaurant
    {
        $owner   = $owner ?? $this->makeUser('owner', null, 'オーナー');
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

    private function makeReservation(User $user, Restaurant $shop): Reservation
    {
        return Reservation::forceCreate([
            'user_id'          => $user->id,
            'restaurant_id'    => $shop->id,
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '12:00',
            'number_of_people' => 2,
            'qr_token'         => 'tok_' . Str::random(24),
        ]);
    }

    /** @test */
    public function guest_must_login_to_reserve_or_review()
    {
        $shop = $this->makeShop();

        $r1 = $this->post(route('reservations.store', $shop));
        $r1->assertRedirect();
        $this->assertTrue(
            in_array($r1->headers->get('Location'), ['http://localhost/menu', 'http://localhost/login']),
            'Unexpected redirect: ' . $r1->headers->get('Location')
        );

        $r2 = $this->post(route('reviews.store'));
        $r2->assertRedirect();
        $this->assertTrue(
            in_array($r2->headers->get('Location'), ['http://localhost/menu', 'http://localhost/login']),
            'Unexpected redirect: ' . $r2->headers->get('Location')
        );
    }

    /** @test */
    public function user_can_post_review_only_for_own_reservation()
    {
        /** @var AuthenticatableContract|User $user */
        $user = $this->makeUser('user', 'user@example.com', 'テストユーザー');
        $shop = $this->makeShop();
        $resv = $this->makeReservation($user, $shop);

        // ビューと同じ：action は route('reviews.store')（パラメータなし）
        // hidden の reservation_id を本文に入れる
        $this->actingAs($user, 'web')
            ->post(route('reviews.store'), [
                'reservation_id' => $resv->id,
                'rating'         => 5,
                'comment'        => '最高！',
            ])
            ->assertRedirect(); // 具体URLには依存しない

        $this->assertDatabaseHas('reviews', [
            'reservation_id' => $resv->id,
            'user_id'        => $user->id,
            'restaurant_id'  => $shop->id,
            'rating'         => 5,
        ]);
    }

    /** @test */
    public function rating_is_required_and_must_be_1_to_5()
    {
        /** @var AuthenticatableContract|User $user */
        $user = $this->makeUser();
        $shop = $this->makeShop();
        $resv = $this->makeReservation($user, $shop);

        $this->actingAs($user, 'web')
            ->from(route('reviews.create', ['reservation' => $resv->id]))
            ->post(route('reviews.store', ['reservation' => $resv->id]), [
                'rating' => null,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['rating']);
    }
}
