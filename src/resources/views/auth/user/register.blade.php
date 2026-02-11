@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')

    <h1 class="title">会員登録</h1>

    <form action="{{ route('register') }}" method="POST" class="register-form">
        @csrf

        {{-- 名前 --}}
        <div class="form-group">
            <label>名前</label>
            <input type="text" name="name" value="{{ old('name') }}">

            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- メールアドレス --}}
        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">

            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password">

            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード確認 --}}
        <div class="form-group">
            <label>パスワード確認</label>
            <input type="password" name="password_confirmation">

            @error('password_confirmation')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="submit-btn">
            登録する
        </button>
    </form>

    <div class="login-link">
        <a href="{{ route('login') }}">ログインはこちら</a>
    </div>

@endsection