<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminOwnerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ルートで auth+role:admin を前提
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:191', 'confirmed'],

            'shop_ids'   => ['required', 'array', 'min:1'],
            'shop_ids.*' => ['integer', Rule::exists('restaurants', 'id')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'password_confirmation' => 'パスワード（確認）',
            'shop_ids' => '担当店舗',
            'shop_ids.*' => '担当店舗',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'name.max' => 'お名前は191文字以内で入力してください。',

            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'email.max' => 'メールアドレスは191文字以内で入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',

            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.max' => 'パスワードは191文字以内で入力してください。',
            'password.confirmed' => 'パスワードと一致しません。',

            'shop_ids.required' => '担当店舗を選択してください。',
            'shop_ids.array' => '担当店舗の形式が不正です。',
            'shop_ids.min' => '担当店舗を1つ以上選択してください。',
            'shop_ids.*.integer' => '担当店舗の指定が不正です。',
            'shop_ids.*.exists' => '選択された担当店舗が存在しません。',
        ];
    }
}
