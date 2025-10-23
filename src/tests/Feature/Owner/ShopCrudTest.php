<?php

namespace Tests\Feature\Owner;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopCrudTest extends TestCase
{
    use RefreshDatabase;

    /** remember_token を触らず認証済みユーザー作成 */
    private function makeUser(string $role = 'owner'): User
    {
        return User::forceCreate([
            'name'              => 'Owner',
            'email'             => uniqid($role . '_', true) . '@example.com',
            'password'          => Hash::make('password123'),
            'role'              => $role,
            'email_verified_at' => now(),
        ]);
    }

    /** timestamps 非依存で area / genre を用意して ID を返す */
    private function makeAreaId(): int
    {
        return (int) DB::table('areas')->insertGetId(['name' => '東京']);
    }
    private function makeGenreId(): int
    {
        return (int) DB::table('genres')->insertGetId(['name' => '和食']);
    }

    /** 既存店舗を作る（factory不要） */
    private function makeShop(User $owner): Restaurant
    {
        return Restaurant::forceCreate([
            'owner_id'    => $owner->id,
            'area_id'     => $this->makeAreaId(),
            'genre_id'    => $this->makeGenreId(),
            'name'        => '既存店舗',
            'description' => '説明',
            'image_url'   => null,
        ]);
    }

    /** @test */
    public function owner_can_create_shop_with_validation()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner');
        $areaId  = $this->makeAreaId();
        $genreId = $this->makeGenreId();

        Storage::fake('public');

        $this->actingAs($owner, 'web')
            ->post(route('owner.shops.store'), [
                'name'        => '新店舗',
                'description' => '説明文',
                'area_id'     => $areaId,
                'genre_id'    => $genreId,
                // ⬇ ここを変更（GD不要のダミー画像）
                'image'       => UploadedFile::fake()->create('shop.jpg', 10, 'image/jpeg'),
            ])
            ->assertRedirect(route('owner.shops.index'));

        $this->assertDatabaseHas('restaurants', [
            'name'     => '新店舗',
            'owner_id' => $owner->id,
        ]);
    }

    /** @test */
    public function store_requires_fields()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner');

        $this->actingAs($owner, 'web')
            ->from(route('owner.shops.create'))
            ->post(route('owner.shops.store'), [])
            ->assertRedirect(route('owner.shops.create'))
            ->assertSessionHasErrors(['name', 'area_id', 'genre_id']); // 画像/説明の必須は要件に合わせて
    }

    /** @test */
    public function owner_can_update_shop_and_replace_image()
    {
        /** @var AuthenticatableContract|User $owner */
        $owner = $this->makeUser('owner');
        $shop  = $this->makeShop($owner);

        Storage::fake('public');

        $newAreaId  = $this->makeAreaId();
        $newGenreId = $this->makeGenreId();

        $this->actingAs($owner, 'web')
            ->put(route('owner.shops.update', $shop), [
                'name'        => '更新後店舗',
                'description' => '更新',
                'area_id'     => $newAreaId,
                'genre_id'    => $newGenreId,
                // ⬇ ここも同様に変更
                'image'       => UploadedFile::fake()->create('new.jpg', 10, 'image/jpeg'),
            ])
            ->assertRedirect(route('owner.shops.index'));

        $this->assertDatabaseHas('restaurants', [
            'id'   => $shop->id,
            'name' => '更新後店舗',
        ]);
    }
}
