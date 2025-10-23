<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Mail\ReservationReminderMail;

class SendReservationReminders extends Command
{
    protected $signature = 'reservations:send-reminders {--date=}';
    protected $description = '送信済みでない当日予約のリマインダーを送る';

    public function handle(): int
    {
        // 基準タイムゾーン（必要なら .env(APP_TIMEZONE) に寄せてもOK）
        $tz = 'Asia/Tokyo';

        // 対象日（--date=YYYY-MM-DD があればそれ、無ければ今日）
        $target = $this->option('date')
            ? Carbon::parse($this->option('date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfDay();

        $todayJst = $target->toDateString();
        $nowJst   = Carbon::now($tz);

        // DATETIME(UTC) で保存されている場合に備え、JST今日のUTCレンジも用意
        $startUtc = $target->copy()->setTimezone('UTC');
        $endUtc   = $target->copy()->endOfDay()->setTimezone('UTC');

        $this->info("Target date (JST): {$todayJst}");

        // 1) reminder_sent_at が null（未送信）
        // 2) reservation_date が DATE(JST) なら whereDate で一致
        //    もしくは DATETIME(UTC) なら JSTの今日に相当するUTCレンジで一致
        $query = Reservation::query()
            ->with(['user', 'restaurant'])
            ->whereNull('reminder_sent_at')
            ->where(function ($q) use ($todayJst, $startUtc, $endUtc) {
                $q->whereDate('reservation_date', $todayJst) // DATE / JST保存想定
                    ->orWhereBetween('reservation_date', [$startUtc, $endUtc]); // UTC DATETIME想定
            });

        $sent = 0;

        // id ベースでチャンク（大量件数でも安全）
        $query->orderBy('id')->chunkById(200, function ($chunk) use (&$sent, $nowJst) {
            foreach ($chunk as $r) {
                // 送信先メールが無ければスキップ
                if (!$r->user?->email) {
                    continue;
                }

                // ---- 表示用の安全な整形（型差異を吸収） ----
                // 日付
                if ($r->reservation_date instanceof \DateTimeInterface) {
                    $dateStr = $r->reservation_date->format('Y-m-d');
                } else {
                    $dateStr = substr((string)$r->reservation_date, 0, 10); // "YYYY-MM-DD..."
                }

                // 時刻 ("HH:MM:SS" や "HHMM" を "HH:MM" に寄せる)
                $rawTime = $r->reservation_time instanceof \DateTimeInterface
                    ? $r->reservation_time->format('H:i')
                    : (string)$r->reservation_time;

                if (preg_match('/^(\d{2}:\d{2})/', $rawTime, $m)) {
                    $timeStr = $m[1];
                } elseif (preg_match('/^(\d{2})(\d{2})$/', $rawTime, $m)) {
                    $timeStr = $m[1] . ':' . $m[2];
                } else {
                    // 不明/空なら一旦 "--:--"
                    $timeStr = '--:--';
                }

                $data = [
                    'userName'        => $r->user->name,
                    'restaurantName'  => $r->restaurant->name ?? '-',
                    'date'            => $dateStr,
                    'time'            => $timeStr,
                    'people'          => (int) $r->number_of_people,
                    'reservationCode' => $r->code ?? null,
                    'manageUrl'       => route('mypage', [], true),
                    'mapUrl'          => $r->restaurant?->address
                        ? 'https://maps.google.com/?q=' . urlencode($r->restaurant->address)
                        : null,
                    'notes'           => null,
                ];

                // 送信（MailHog/SMTP 等、環境に応じて）
                Mail::to($r->user->email)->send(new ReservationReminderMail($data));
                // 大量配信の場合は queue() 推奨：
                // Mail::to($r->user->email)->queue(new ReservationReminderMail($data));

                // 送信済みフラグ（JST時刻で記録）
                $r->forceFill(['reminder_sent_at' => $nowJst])->save();

                $sent++;
            }
        });

        $this->info("Reminders sent: {$sent}");
        return self::SUCCESS;
    }
}
