<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'メールアドレスを入力してください。',
            'email.email'       => 'メールアドレスの形式が正しくありません。',
            'password.required' => 'パスワードを入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'email'    => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }

    /**
     * 認証失敗時のメッセージをフィールド別に表示
     */
    public function authenticate(): void
    {
        $remember = $this->boolean('remember');

        if (! Auth::attempt($this->only('email', 'password'), $remember)) {
            throw ValidationException::withMessages([
                'email'    => 'メールアドレスが正しくありません。',
                'password' => 'パスワードが正しくありません。',
            ]);
        }
    }
}
