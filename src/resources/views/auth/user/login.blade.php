@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/auth/user/login.css') }}">
@endsection

@section('content')

<div class="register-wrapper">

    <h1 class="page-title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- メールアドレス -->
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- パスワード -->
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- ログインボタン -->
        <button type="submit" class="login-button">ログインする</button>

        <!-- 会員登録リンク -->
        <p class="register-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </p>

        @if(session('status') === 'login-error')
            <p class="error-message">ログイン情報が登録されていません</p>
        @endif

    </form>

</div>

@endsection

