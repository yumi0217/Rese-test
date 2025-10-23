<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_with_valid_inputs_and_redirects_to_verification_notice()
    {
        $res = $this->post('/register', [
            'name'                  => '山田太郎',
            'email'                 => 'taro@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // アプリはメール認証を有効化しているため /email/verify へリダイレクト
        $res->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', ['email' => 'taro@example.com']);
    }

    /** @test */
    public function it_validates_required_fields_and_password_rule()
    {
        $res = $this->from('/register')->post('/register', [
            'name'                  => '',
            'email'                 => '',
            'password'              => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
