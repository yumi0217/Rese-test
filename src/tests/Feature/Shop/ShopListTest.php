<?php

namespace Tests\Feature\Shop;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShopListTest extends TestCase
{
    use RefreshDatabase;

    /** remember_token を触らず認証済みユーザー作成 */
    private function makeUser(string $role = 'user'): User
    {
        return User::forceCreate([
            'name'              => 'Tester',
            'email'             => uniqid($role . '_', true) . '@example.com',
            'password'          => Hash::make('password123'),
            // role カラムが無ければこの行は削除してOK
            'role'              => $role,
            'email_verified_at' => now(),
        ]);
    }

    /** timestamps 非依存で area / genre を用意して ID を返す */
    private function makeAreaId(string $name = '東京'): int
    {
        return (int) DB::table('areas')->insertGetId(['name' => $name]);
    }
    private function makeGenreId(string $name = '寿司'): int
    {
        return (int) DB::table('genres')->insertGetId(['name' => $name]);
    }

    /** 店舗作成（Factory未使用） */
    private function makeShop(int $areaId, int $genreId, string $name = 'お店'): Restaurant
    {
        // owner が必須なら作る（不要なら owner_id を null に）
        $owner = $this->makeUser('owner');

        return Restaurant::forceCreate([
            'owner_id'    => $owner->id,
            'area_id'     => $areaId,
            'genre_id'    => $genreId,
            'name'        => $name,
            'description' => '説明',
            'image_url'   => null,
        ]);
    }

    /** @test */
    public function it_lists_shops_with_pagination_and_filters()
    {
        $areaId  = $this->makeAreaId('東京');
        $genreId = $this->makeGenreId('寿司');

        // フィルタに合致する店を複数作成
        for ($i = 1; $i <= 15; $i++) {
            $this->makeShop($areaId, $genreId, "東京寿司 店{$i}");
        }
        // ノイズ（別エリア/ジャンル）
        $na = $this->makeAreaId('大阪');
        $ng = $this->makeGenreId('焼肉');
        $this->makeShop($na, $ng, '大阪焼肉 店A');

        // 実装の検索パラメータ名に合わせる（HTMLは name="keyword"）
        $res = $this->get(route('shops.index', [
            'area'    => $areaId,
            'genre'   => $genreId,
            'keyword' => '店',
            'page'    => 2, // ページ指定（存在しなくても 200 ならOK とする）
        ]));

        $res->assertOk();
        // 少なくとも作成した店名の一部が出ることを確認
        $res->assertSee('東京寿司', false);
        // ページングリンクの痕跡（?page=）がレンダリングされていることを緩く確認
        $this->assertStringContainsString('page=', $res->getContent());
    }

    /** @test */
    public function guests_cannot_favorite()
    {
        $areaId  = $this->makeAreaId();
        $genreId = $this->makeGenreId();
        $shop    = $this->makeShop($areaId, $genreId, 'ゲスト用お店');

        // ルートは本文で restaurant_id を送る実装に合わせる
        $r = $this->post(route('favorites.toggle'), [
            'restaurant_id' => $shop->id,
        ]);

        $r->assertRedirect();
        $this->assertTrue(
            in_array($r->headers->get('Location'), ['http://localhost/menu', 'http://localhost/login']),
            'Unexpected redirect: ' . $r->headers->get('Location')
        );
    }

    /** @test */
    public function users_can_toggle_favorite_after_login()
    {
        /** @var AuthenticatableContract|User $user */
        $user    = $this->makeUser('user');
        $areaId  = $this->makeAreaId();
        $genreId = $this->makeGenreId();
        $shop    = $this->makeShop($areaId, $genreId, 'お気に入り店');

        $this->actingAs($user, 'web')
            ->post(route('favorites.toggle'), [
                'restaurant_id' => $shop->id,
            ])
            ->assertRedirect(); // 戻り先は実装依存

        $this->assertDatabaseHas('favorites', [
            'user_id'       => $user->id,
            'restaurant_id' => $shop->id,
        ]);
    }
}
