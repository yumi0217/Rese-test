<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Models\Reservation;

class PaymentController extends Controller
{
    public function checkout(Reservation $reservation, Request $request)
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);

        $user = $request->user();

        // APIバージョンも明示して安定化
        $stripe = new \Stripe\StripeClient([
            'api_key'        => config('services.stripe.secret'),
            'stripe_version' => '2022-11-15',
        ]);

        // Customer 作成（必要な場合）
        if (empty($user->stripe_customer_id)) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name'  => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $fallback = url('detail/' . $reservation->restaurant_id);
        $return   = $request->query('return', $fallback);

        // ★ setupモード用の最小ペイロード（line_items は入れない）
        $payload = [
            'mode'                  => 'setup',
            'customer'              => $user->stripe_customer_id, // 無くてもOK
            'payment_method_types'  => ['card'],                  // 明示
            'success_url'           => route('payments.success', [
                'session_id' => '{CHECKOUT_SESSION_ID}',
                'return'     => $return,
            ], true),
            'cancel_url'            => route('payments.cancel', ['return' => $return], true),
            'metadata'              => [
                'reservation_id' => (string) $reservation->id,
                'user_id'        => (string) $user->id,
            ],
        ];

        // 念のための保険：混入していたら除去
        unset($payload['line_items']);

        $session = $stripe->checkout->sessions->create($payload);

        return redirect()->away($session->url);
    }


    public function success(Request $request)
    {
        if ($sid = $request->query('session_id')) {
            $stripe  = new StripeClient(config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve($sid);
            if ($session?->setup_intent) {
                $setup = $stripe->setupIntents->retrieve($session->setup_intent);
                $pm    = $setup->payment_method ?? null;
                if ($pm && $request->user()) {
                    $request->user()->forceFill(['stripe_payment_method' => $pm])->save();
                }
            }
        }
        return redirect()->to($request->query('return', url('/')))
            ->with('status', 'Stripeから戻りました。');
    }

    public function cancel(Request $request)
    {
        return redirect()->to($request->query('return', url('/')))
            ->with('status', 'Stripeの手続きをキャンセルしました。');
    }
}
