{{-- resources/views/mail/owner_broadcast.blade.php --}}
<!doctype html>
<html lang="ja">

<body style="margin:0;padding:0;background:#f6f8fb;font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,'Noto Sans JP',sans-serif;">
    <div style="max-width:640px;margin:24px auto;background:#fff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
            <h1 style="margin:0;font-size:16px;font-weight:700;color:#111827;">
                {{ $subjectText ?? 'お知らせ' }}
            </h1>
        </div>
        <div style="padding:20px;font-size:14px;line-height:1.8;color:#111827;">
            {!! nl2br(e($bodyText)) !!}
        </div>
        <div style="padding:14px 20px;border-top:1px solid #f1f5f9;font-size:12px;color:#6b7280;">
            本メールは送信専用です。ご返信いただいてもお答えできない場合があります。<br>
            {{-- 署名は任意 --}}
            {{ $senderName ?? config('app.name') }}
        </div>
    </div>
</body>

</html>