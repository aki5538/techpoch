@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance/clock.css') }}">
@endsection

@section('header-menu')
    <nav class="attendance-header-menu">
        <a href="{{ route('user.attendance') }}">勤怠</a>
        <a href="{{ route('user.attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請</a>

        <a href="#"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            ログアウト
        </a>

        <form id="logout-form"
            action="{{ route('logout') }}"
            method="POST"
            style="display:none;">
            @csrf
        </form>
    </nav>
@endsection

@section('content')

    <div class="attendance-container">

    <div class="attendance-inner">

        <div class="status-badge">
            <span>{{ $status }}</span>
        </div>

        <div class="attendance-date">
            {{ $today }}
        </div>

        <div class="attendance-time">
            {{ $now }}
        </div>

        <div class="attendance-buttons">

            @if ($status === '勤務外')
                <form method="POST" action="{{ route('user.clock.in') }}">
                    @csrf
                    <button type="submit" class="clockin-btn">
                        <span>出勤</span>
                    </button>
                </form>
            @endif

            @if ($status === '出勤中')
                <form method="POST" action="{{ route('user.break.in') }}">
                    @csrf
                    <button type="submit" class="breakin-btn">
                        <span>休憩入</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('user.clock.out') }}">
                    @csrf
                    <button type="submit" class="clockout-btn">
                        <span>退勤</span>
                    </button>
                </form>
            @endif

            @if ($status === '休憩中')
                <form method="POST" action="{{ route('user.break.out') }}">
                    @csrf
                    <button type="submit" class="breakout-btn">
                        <span>休憩戻</span>
                    </button>
                </form>
            @endif

            @if ($status === '退勤済')
                <p class="finished-message">お疲れ様でした。</p>
            @endif

        </div>
    </div>

@endsection