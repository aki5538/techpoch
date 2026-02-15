<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証のお願い</title>
    <link rel="stylesheet" href="/css/auth/user/verify-email.css">
</head>
<body>

<div class="verify-wrapper">

    <!-- Figma 準拠メッセージ -->
    <div class="verify-message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </div>

    <a href="{{ route('verification.notice') }}" class="verify-button">
        <span class="verify-button-text">認証はこちらから</span>
    </a>

    <!-- 認証メール再送（仕様書 FN012） -->
    <a href="{{ route('verification.send') }}"
        onclick="event.preventDefault(); document.getElementById('resend-form').submit();"
        class="resend-link">
            認証メールを再送する
    </a>

    <form id="resend-form" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>
</div>

</body>
</html>