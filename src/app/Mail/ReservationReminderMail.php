<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
// キュー運用したいなら下を有効化：use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationReminderMail extends Mailable /* implements ShouldQueue */
{
    use Queueable, SerializesModels;

    /** @var array */
    public array $data;

    /**
     * @param array $data 例:
     * [
     *   'userName' => '山田太郎',
     *   'restaurantName' => 'Rese 新宿店',
     *   'date' => '2025-10-09',
     *   'time' => '19:00',
     *   'people' => 2,
     *   'reservationCode' => 'ABC123',   // 任意
     *   'manageUrl' => 'https://example.com/mypage/reservations/1', // 任意
     *   'mapUrl' => 'https://maps.google.com/?q=...',
     *   'notes' => "遅れる場合はご連絡ください"
     * ]
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        // 件名をここで固定にするなら：
        $this->subject('本日のご予約リマインダー');
    }

    public function build()
    {
        return $this
            // 件名を動的にするならここでもOK
            // ->subject('本日のご予約リマインダー')

            // HTMLテンプレ
            ->view('emails.reservation_reminder', $this->data)

            // プレーンテキスト（用意した人だけ）
            ->text('emails.reservation_reminder_plain', $this->data);
    }
}
