<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ルート側で auth 済み想定
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'rating'         => ['required', 'integer', 'between:1,5'],
            'comment'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reservation_id' => '予約ID',
            'rating'         => '評価',
            'comment'        => 'コメント',
        ];
    }

    public function messages(): array
    {
        return [
            // 未入力
            'rating.required' => '評価を選択してください。',
            // 形式
            'rating.integer'  => '評価の形式が不正です。',
            'rating.between'  => '評価は1〜5の範囲で選択してください。',
            'comment.string'  => 'コメントの形式が不正です。',
            'comment.max'     => 'コメントは255文字以内で入力してください。',
        ];
    }
}
