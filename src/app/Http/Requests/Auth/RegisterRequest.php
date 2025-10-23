<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:191'],
            'email'    => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'ユーザーネームを入力してください。',
            'name.string'       => 'ユーザーネームは文字列で入力してください。',
            'name.max'          => 'ユーザーネームは:max文字以内で入力してください。',

            'email.required'    => 'メールアドレスを入力してください。',
            'email.string'      => 'メールアドレスは文字列で入力してください。',
            'email.email'       => 'メールアドレスの形式が正しくありません。',
            'email.max'         => 'メールアドレスは:max文字以内で入力してください。',
            'email.unique'      => 'このメールアドレスは既に登録されています。',

            'password.required' => 'パスワードを入力してください。',
            'password.string'   => 'パスワードは文字列で入力してください。',
            'password.min'      => 'パスワードは:min文字以上で入力してください。',
            'password.max'      => 'パスワードは:max文字以内で入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'     => 'ユーザーネーム',
            'email'    => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }
}
