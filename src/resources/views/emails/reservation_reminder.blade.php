<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- メールは外部CSSが無効なことが多いので、基本はこの<style>を使う -->
    <style>
        /* ===== Reservation Reminder (inline safe) ===== */
        body {
            margin: 0;
            padding: 0;
            background: #f6f8fb;
            -webkit-text-size-adjust: 100%
        }

        .wrap {
            max-width: 640px;
            margin: 24px auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden
        }

        .header {
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f7
        }

        .brand {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #111827
        }

        .hero {
            padding: 16px 20px 0
        }

        .title {
            margin: 0 0 4px;
            font-size: 18px;
            color: #111827;
            font-weight: 800
        }

        .lead {
            margin: 0 0 16px;
            color: #374151;
            font-size: 14px;
            line-height: 1.8
        }

        .card {
            margin: 12px 20px;
            background: #f9fbff;
            border: 1px solid #e6ecf6;
            border-radius: 10px;
            padding: 12px
        }

        .row {
            display: flex;
            gap: 8px;
            margin: 6px 0;
            font-size: 14px;
            color: #111827
        }

        .key {
            min-width: 88px;
            color: #6b7280
        }

        .val {
            flex: 1
        }

        .btns {
            padding: 8px 20px 20px
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: #fff !important;
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 800;
            border: 1px solid #1d4ed8
        }

        .btn+.btn {
            margin-left: 8px
        }

        .note {
            padding: 0 20px 16px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.7
        }

        .footer {
            padding: 14px 20px;
            border-top: 1px solid #eef2f7;
            color: #6b7280;
            font-size: 12px
        }

        /* モバイル調整 */
        @media (max-width:480px) {
            .row {
                display: block
            }

            .key {
                display: block;
                margin-bottom: 2px
            }

            .btn {
                display: block;
                text-align: center;
                margin: 8px 0 0
            }

            .btn+.btn {
                margin-left: 0
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="header">
            <h1 class="brand">{{ config('app.name', 'Rese') }}</h1>
        </div>

        <div class="hero">
            <h2 class="title">本日ご予約のリマインダー</h2>
            <p class="lead">
                {{ $userName ?? 'お客様' }}、本日のご来店予約のご案内です。内容をご確認ください。
            </p>
        </div>

        <div class="card">
            <div class="row">
                <div class="key">店舗名</div>
                <div class="val">{{ $restaurantName ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="key">日付</div>
                <div class="val">{{ $date ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="key">時刻</div>
                <div class="val">{{ $time ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="key">人数</div>
                <div class="val">{{ $people ?? '-' }}名</div>
            </div>
            @isset($reservationCode)
            <div class="row">
                <div class="key">予約番号</div>
                <div class="val">{{ $reservationCode }}</div>
            </div>
            @endisset
        </div>

        <div class="btns">
            @isset($manageUrl)
            <a class="btn" href="{{ $manageUrl }}">予約内容を確認</a>
            @endisset
            @isset($mapUrl)
            <a class="btn" href="{{ $mapUrl }}">地図を開く</a>
            @endisset
        </div>

        @isset($notes)
        <div class="note">
            {!! nl2br(e($notes)) !!}
        </div>
        @endisset

        <div class="footer">
            このメールは送信専用です。ご不明点は店舗までお問い合わせください。<br>
            &copy; {{ date('Y') }} {{ config('app.name', 'Rese') }}
        </div>
    </div>
</body>

</html>