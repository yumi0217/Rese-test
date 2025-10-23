cat > resources/views/emails/reservation_reminder_plain.blade.php <<'PLAINTXT'
    {{ $userName ?? 'お客様' }} 様

    本日ご予約のご案内です。

    店舗名：{{ $restaurantName ?? '-' }}
    日付　：{{ $date ?? '' }}
    時刻　：{{ $time ?? '' }}
    人数　：{{ $people ?? '' }}名
    @isset($reservationCode)
    予約番号：{{ $reservationCode }}
    @endisset
    @isset($manageUrl)
    予約確認：{{ $manageUrl }}
    @endisset
    @isset($mapUrl)
    地図　　：{{ $mapUrl }}
    @endisset

    ※このメールは送信専用です。
    PLAINTXT