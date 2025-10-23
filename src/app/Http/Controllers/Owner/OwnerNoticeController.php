<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Mail\OwnerBroadcastMail;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Owner\EmailNoticeRequest; // ★ 追加

class OwnerNoticeController extends Controller
{
    // 送信フォーム表示
    public function create(Request $request)
    {
        $owner = Auth::user();
        if (! $owner) {
            abort(401);
        }

        // オーナー配下の店舗ID
        $restaurantIds = method_exists($owner, 'shops')
            ? $owner->shops()->pluck('id')
            : Restaurant::where('owner_id', $owner->id)->pluck('id');

        // その店舗を利用したユーザー（重複排除）
        $users = User::whereIn('id', function ($q) use ($restaurantIds) {
            $q->select('user_id')
                ->from('reservations')
                ->whereIn('restaurant_id', $restaurantIds);
        })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('owners.notice.create', compact('users'));
    }

    // 送信処理
    public function send(EmailNoticeRequest $request) // ★ リクエストクラスで検証
    {
        $data = $request->validated();

        // 宛先ID配列（to_user_ids[]）
        $recipients = User::whereIn('id', $data['to_user_ids'])->get(['name', 'email']);

        foreach ($recipients as $u) {
            if (! $u->email) continue;

            Mail::to($u->email)->send(
                new OwnerBroadcastMail($data['subject'], $data['body'])
            );
            // 件数が多い想定なら ->queue() に変更してキュー使用を推奨
        }

        return back()->with('status', 'お知らせメールを送信しました（' . $recipients->count() . '件）');
    }
}
