<?php

namespace Tests\Feature\Owner;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrVerifyTest extends TestCase
{
    use RefreshDatabase;

    /** remember_token を触らず、メール認証済みのユーザーを作る */
    private function makeUser(string $role = 'user', ?string $email = null, string $name = 'テストユーザー'): User
    {
        return User::forceCreate([
            'name'              => $name,
            'email'             => $email ?? uniqid($role . '_', true) . '@example.com',
            'password'          => Hash::make('password123'),
            'role'              => $role,
            'email_verified_at' => now(),
        ]);
    }

    /** Area/Genre を直 insert して店舗を作る（timestamps 非依存） */
    private function makeShop(User $owner, string $name = '店舗'): Restaurant
    {
        $areaId  = DB::table('areas')->insertGetId(['name' => '東京']);
        $genreId = DB::table('genres')->insertGetId(['name' => '和食']);

        return Restaurant::forceCreate([
            'owner_id'    => $owner->id,
            'area_id'     => $areaId,
            'genre_id'    => $genreId,
            'name'        => $name,
            'description' => '説明',
            'image_url'   => null,
        ]);
    }

    /** 予約を作成（必要最低限） */
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
    public function owner_can_verify_qr_code_for_own_shop()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner', 'owner@example.com', 'オーナー');
        $user  = $this->makeUser('user', 'user@example.com', '利用者');
        $shop  = $this->makeShop($owner, '自店');

        $resv = $this->makeReservation($user, $shop);
        $code = "RSV:{$resv->id}:{$resv->qr_token}";

        // 成功時に 302 → 一覧/詳細へリダイレクトしてもOKにする
        $this->actingAs($owner, 'web')
            ->followingRedirects()
            ->post(route('owners.qr.verify.post'), [
                'code'     => $code,
                'qr'       => $code,
                'qr_code'  => $code,
            ])
            ->assertOk()
            ->assertSee($shop->name);
    }

    /** @test */
    public function verify_rejects_invalid_code()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner', 'owner2@example.com', 'オーナー2');

        // 戻り先はフォームGETのルートに統一
        $this->actingAs($owner, 'web')
            ->from(route('owners.qr.verify'))
            ->post(route('owners.qr.verify.post'), [
                'code'     => 'BADCODE',
                'qr'       => 'BADCODE',
                'qr_code'  => 'BADCODE',
            ])
            ->assertRedirect(route('owners.qr.verify'))
            ->assertSessionHasErrors();
    }
}
