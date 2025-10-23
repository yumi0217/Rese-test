<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_errors_when_required_fields_are_missing()
    {
        $res = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => '',
            'expected_role' => 'user',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function it_shows_error_when_password_is_wrong()
    {
        // Factoryを使わず、remember_token を一切含めないINSERT
        $user = User::forceCreate([
            'name'              => 'テスト太郎',
            'email'             => 'u@example.com',
            'password'          => Hash::make('correct-pass'),
            'role'              => 'user',
            'email_verified_at' => now(),
        ]);

        $res = $this->from('/login')->post('/login', [
            'email' => 'u@example.com',
            'password' => 'wrong',
            'expected_role' => 'user',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** @test */
    public function it_redirects_by_role_after_login()
    {
        // こちらも forceCreate で remember_token を回避
        $user = User::forceCreate([
            'name'              => 'ユーザー',
            'email'             => 'user@example.com',
            'password'          => Hash::make('pass12345'),
            'role'              => 'user',
            'email_verified_at' => now(),
        ]);

        $res = $this->post('/login', [
            'email' => $user->email,
            'password' => 'pass12345',
            'expected_role' => 'user',
        ]);

        $res->assertRedirect(route('shops.index'));
        $this->assertAuthenticatedAs($user);
    }
}
