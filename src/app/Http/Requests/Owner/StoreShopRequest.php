<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ルートで role:owner を掛けていればOK
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'area_id'     => ['required', 'integer'],               // 必要なら exists:areas,id
            'genre_id'    => ['required', 'integer'],               // 必要なら exists:genres,id
            'description' => ['required', 'string', 'max:1000'],
            'image'       => ['required', 'image', 'mimes:jpeg,png,webp', 'max:4096'], // 4MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => '店名を入力してください',
            'area_id.required'     => 'エリアを選択してください',
            'genre_id.required'    => 'ジャンルを選択してください',
            'description.required' => '説明を入力してください',
            'image.required'       => '画像を選択してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => '店名',
            'area_id'     => 'エリア',
            'genre_id'    => 'ジャンル',
            'description' => '説明',
            'image'       => '画像',
        ];
    }
}
