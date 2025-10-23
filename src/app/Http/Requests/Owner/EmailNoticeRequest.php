<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class EmailNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ルートで auth+role:owner を掛けている想定なので true
        return true;
    }

    public function rules(): array
    {
        return [
            // 宛先（配列必須）
            'to_user_ids'   => ['required', 'array', 'min:1'],
            'to_user_ids.*' => ['integer', 'exists:users,id'],

            // 件名/本文
            'subject' => ['required', 'string', 'max:191'],
            'body'    => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'to_user_ids'   => '送信先',
            'to_user_ids.*' => '送信先',
            'subject'       => '件名',
            'body'          => '本文',
        ];
    }

    public function messages(): array
    {
        return [
            'to_user_ids.required' => '送信先を選択してください。',
            'to_user_ids.array'    => '送信先の形式が不正です。',
            'to_user_ids.min'      => '送信先を1件以上選択してください。',
            'to_user_ids.*.integer' => '送信先の指定が不正です。',
            'to_user_ids.*.exists' => '送信先のユーザーが存在しません。',

            'subject.required' => '件名を入力してください。',
            'subject.string'   => '件名の形式が不正です。',
            'subject.max'      => '件名は191文字以内で入力してください。',

            'body.required' => '本文を入力してください。',
            'body.string'   => '本文の形式が不正です。',
            'body.max'      => '本文は5000文字以内で入力してください。',
        ];
    }
}
