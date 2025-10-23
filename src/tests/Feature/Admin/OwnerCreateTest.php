<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_owner_creation_inputs()
    {
        /** @var AuthenticatableContract|User $admin */
        $admin = User::forceCreate([
            'name'              => '管理者',
            'email'             => 'admin@example.com',
            'password'          => Hash::make('secret123'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');

        $res = $this->from(route('admin.owners.create'))
            ->post(route('admin.owners.store'), [
                'name'                  => '',
                'email'                 => 'bad',
                'password'              => 'short',
                'password_confirmation' => 'mismatch',
                'shop_ids'              => [], // 必須 or min:1 の想定
            ]);

        $res->assertRedirect(route('admin.owners.create'));
        $res->assertSessionHasErrors(['name', 'email', 'password', 'shop_ids']);
    }
}
