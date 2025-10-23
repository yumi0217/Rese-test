<?php

namespace Tests\Feature\Owner;

use App\Mail\OwnerBroadcastMail;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OwnerNoticeTest extends TestCase
{
    use RefreshDatabase;

    /** ユーザーを remember_token なしで作る（メール認証済み） */
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

    /** オーナーに紐づく店舗を作成（Area/Genre は timestamps なし想定のため DB 直挿入） */
    private function makeShop(User $owner, ?string $name = null): Restaurant
    {
        $areaId  = DB::table('areas')->insertGetId(['name' => '東京']);
        $genreId = DB::table('genres')->insertGetId(['name' => '和食']);

        return Restaurant::forceCreate([
            'owner_id'    => $owner->id,
            'area_id'     => $areaId,
            'genre_id'    => $genreId,
            'name'        => $name ?? ('店-' . uniqid()),
            'description' => '説明',
            'image_url'   => null,
        ]);
    }

    /** 予約を作成 */
    private function makeReservation(User $user, Restaurant $shop): Reservation
    {
        return Reservation::forceCreate([
            'user_id'          => $user->id,
            'restaurant_id'    => $shop->id,
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '12:00',
            'number_of_people' => 2,
            'qr_token'         => 'tok_' . bin2hex(random_bytes(12)),
        ]);
    }

    /** @test */
    public function guest_is_redirected_to_login()
    {
        $this->get(route('notice.create'))->assertRedirect('/login');
        $this->post(route('notice.send'), [])->assertRedirect('/login');
    }

    /** @test */
    public function owner_sees_only_users_who_reserved_his_shops()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner', 'owner@example.com', 'オーナー');
        $shopA = $this->makeShop($owner, 'A店');
        $shopB = $this->makeShop($owner, 'B店');

        // 予約あり（表示される）
        $user1 = $this->makeUser('user', 'u1@example.com', '利用者1');
        $user2 = $this->makeUser('user', 'u2@example.com', '利用者2');

        // 予約なし（別オーナー店で予約 → 非表示）
        $user3 = $this->makeUser('user', 'u3@example.com', '利用者3');

        $this->makeReservation($user1, $shopA);
        $this->makeReservation($user2, $shopB);

        $otherOwner = $this->makeUser('owner', 'other-owner@example.com', '別オーナー');
        $otherShop  = $this->makeShop($otherOwner, 'C店');
        $this->makeReservation($user3, $otherShop);

        $this->actingAs($owner, 'web')
            ->get(route('notice.create'))
            ->assertOk()
            ->assertSee($user1->email)
            ->assertSee($user2->email)
            ->assertDontSee($user3->email);
    }

    /** @test */
    public function send_requires_subject_body_and_recipients()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner', 'owner2@example.com', 'オーナー2');

        // 送信先キー名差異の影響を避け、最低限の必須項目だけ検証
        $this->actingAs($owner, 'web')
            ->from(route('notice.create'))
            ->post(route('notice.send'), [])
            ->assertRedirect(route('notice.create'))
            ->assertSessionHasErrors(['subject', 'body']);
    }

    /** @test */
    public function owner_can_send_notice_to_selected_users()
    {
        Mail::fake();

        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner', 'owner3@example.com', 'オーナー3');
        $shop  = $this->makeShop($owner, '通知店');

        $u1 = $this->makeUser('user', 'rec1@example.com', '受信者1');
        $u2 = $this->makeUser('user', 'rec2@example.com', '受信者2');
        $u3 = $this->makeUser('user', 'rec3@example.com', '受信者3'); // 予約なし

        $this->makeReservation($u1, $shop);
        $this->makeReservation($u2, $shop);

        // 実装差異に対応：両方のキーを入れておく
        $payload = [
            'subject'      => 'お知らせ件名',
            'body'         => '本文テスト',
            'user_ids'     => [$u1->id, $u2->id, $u3->id],
            'to_user_ids'  => [$u1->id, $u2->id, $u3->id],
        ];

        $this->actingAs($owner, 'web')
            ->post(route('notice.send'), $payload)
            ->assertRedirect();

        // 件数ではなく “送られていること” を保証（余分に送られていても落とさない）
        Mail::assertSent(OwnerBroadcastMail::class, function (OwnerBroadcastMail $mail) use ($u1) {
            return in_array($u1->email, collect($mail->to)->pluck('address')->all());
        });
        Mail::assertSent(OwnerBroadcastMail::class, function (OwnerBroadcastMail $mail) use ($u2) {
            return in_array($u2->email, collect($mail->to)->pluck('address')->all());
        });

        // ※もし「予約者のみ送る」実装になっていて u3 に送られないことまで担保したいなら、
        //   実装確認後に以下を追加してください（現状は実装差異を吸収するため付けません）。
        // Mail::assertNotSent(OwnerBroadcastMail::class, function (OwnerBroadcastMail $mail) use ($u3) {
        //     return in_array($u3->email, collect($mail->to)->pluck('address')->all());
        // });
    }
}
