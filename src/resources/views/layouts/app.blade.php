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
    width: 100%;
    max-width: 1260px;
    height: 80px;
    background-color: #000000;

    margin: 0 auto;           /* ★ 中央寄せ */
    position: relative;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 40px;          /* Figma の左右余白 */
}


        header img {
            width: 370px;
            height: 36px;
        }
    </style>
</head>
<body>

    <!-- 黒帯ヘッダー（ロゴのみ） -->
    <header class="global-header">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ (1).png') }}" alt="COACHTECH">
        @yield('header-menu')
    </header>

    {{-- 各画面の内容 --}}
    @yield('content')

    {{-- 画面ごとのJS --}}
    @yield('script')

</body>
</html>