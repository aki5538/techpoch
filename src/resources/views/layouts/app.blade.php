<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>

    <style>
        body {
            background-color: #f3f3f3;
            margin: 0;
            font-family: sans-serif;
        }

        header {
            width: 100%;
            height: 80px; /* Figma の高さ */
            background-color: #000000;
            display: flex;
            justify-content: flex-start;
            align-items: center;
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