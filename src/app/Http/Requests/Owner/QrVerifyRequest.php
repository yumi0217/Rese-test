<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class QrVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ルート側で auth + role:owner を掛けている前提
        return true;
    }

    /**
     * URLで貼られてもRSV生文字列でもOKにするための正規化
     */
    protected function prepareForValidation(): void
    {
        $raw = trim((string) $this->input('code', ''));

        // URLが入ってきた場合 (?code=...) を抽出して urldecode
        if (preg_match('/^https?:\/\/.+?[?&]code=([^&]+)/i', $raw, $m)) {
            $raw = urldecode($m[1]);
        }

        // 全角コロン対策など
        $raw = str_replace(['：'], [':'], $raw);

        $this->merge(['code' => $raw]);
    }

    public function rules(): array
    {
        return [
            // ★ トークンは base64相当（英数 + _ - ＝）に変更
            //   桁数は20〜128あたりで許容（必要なら調整）
            'code' => ['required', 'string', 'max:512', 'regex:/^RSV:(\d+):([A-Za-z0-9_\-=]{20,128})$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => '読み取り結果を入力してください。',
            'code.regex'    => 'QRコードの形式が不正です。「RSV:{予約ID}:{トークン}」の形式で入力してください。',
        ];
    }

    public function attributes(): array
    {
        return ['code' => '読み取り結果'];
    }

    /**
     * 検証後にID/トークンを取り出してコントローラで使いやすくする
     */
    protected function passedValidation(): void
    {
        if (preg_match('/^RSV:(\d+):([A-Za-z0-9_\-=]{20,128})$/', $this->input('code'), $m)) {
            $this->merge([
                'reservation_id' => (int) $m[1],
                'qr_token'       => $m[2],
            ]);
        }
    }
}
