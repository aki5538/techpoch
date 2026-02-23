@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin/login.css') }}">
@endsection

@section('content')
<div class="login-container">
    <div class="admin-login-wrapper">

        {{-- タイトル --}}
        <h1 class="admin-login-title">管理者ログイン</h1>

        {{-- ログイン情報が誤っている場合（仕様書 FN016） --}}
        @if ($errors->has('login'))
            <p class="admin-login-error-login error-message">
                {{ $errors->first('login') }}
            </p>
        @endif

        <form method="POST" action="{{ url('/admin/login') }}">
            @csrf

            {{-- メールアドレス --}}
            <label for="email" class="admin-login-label-email">メールアドレス</label>
            <input
                id="email"
                type="email"
                name="email"
                class="admin-login-input-email"
                value="{{ old('email') }}"
            >
            @error('email')
                <p class="admin-login-error-email error-message">{{ $message }}</p>
            @enderror

            {{-- パスワード --}}
            <label for="password" class="admin-login-label-password">パスワード</label>
            <input
                id="password"
                type="password"
                name="password"
                class="admin-login-input-password"
            >
            @error('password')
                <p class="admin-login-error-password error-message">{{ $message }}</p>
            @enderror

            {{-- ボタン --}}
            <button type="submit" class="admin-login-button">
                <span class="admin-login-button-text">管理者ログインする</span>
            </button>

        </form>

    </div>
</div>
@endsection