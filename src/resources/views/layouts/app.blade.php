<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>

    @yield('head')

    @yield('css')

    <style>
        header {
    width: 100%;
    max-width: 1260px;
    height: 80px;
    background-color: #000000;

    margin: 0 auto;
    position: relative;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 40px;
}


        header img {
            width: 370px;
            height: 36px;
        }
    </style>
</head>
<body>

    <header class="global-header">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ (1).png') }}" alt="COACHTECH">
        @yield('header-menu')
    </header>

    @yield('content')

    @yield('script')

</body>
</html>