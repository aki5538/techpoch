<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>

    {{-- 画面ごとの追加ヘッダー（必要なら） --}}
    @yield('head')

    {{-- 画面ごとのCSS --}}
    @yield('css')

    <style>
        header {
            width: 1512px;
            height: 80px;
            background-color: #000000;
        }
        header img {
            width: 370px;
            height: 36px;
            position: absolute;
            top: 22px;
            left: 25px;
        }
    </style>
</head>
<body>

    <!-- 黒帯ヘッダー（ロゴのみ） -->
    <header>
        <img src="{{ asset('images/COACHTECHヘッダーロゴ (1).png') }}" alt="COACHTECH">
    </header>

    {{-- 各画面の内容 --}}
    @yield('content')

    {{-- 画面ごとのJS --}}
    @yield('script')

</body>
</html>