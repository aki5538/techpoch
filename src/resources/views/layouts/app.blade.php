<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>

    @yield('head')
    
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

    <!-- 黒帯ヘッダー -->
    <header>
        <img src="{{ asset('images/COACHTECHヘッダーロゴ (1).png') }}" alt="COACHTECH">
    </header>

    @yield('content')
</body>
</html>