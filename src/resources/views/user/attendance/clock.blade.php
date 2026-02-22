@extends('layouts.app')

@section('content')

{{-- 黒帯の中の右上メニュー --}}
    <nav class="attendance-header-menu">
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        <a href="{{ route('logout') }}">ログアウト</a>
    </nav>

    <div class="attendance-container">

        {{-- ステータス表示 --}}
        <div class="status-badge">
            <span>{{ $status }}</span>
        </div>

        {{-- 日付表示 --}}
        <div class="attendance-date">
            {{ $today }}
        </div>

        {{-- 時刻表示 --}}
        <div class="attendance-time">
            {{ $now }}
        </div>

        {{-- ボタン出し分け --}}
        <div class="attendance-buttons">

            {{-- 勤務外 → 出勤ボタン --}}
            @if ($status === '勤務外')
                <form method="POST" action="{{ route('user.clock.in') }}">
                    @csrf
                    <button type="submit" class="clockin-btn">
                        <span>出勤</span>
                    </button>
                </form>
            @endif

            {{-- 出勤中 → 休憩入／退勤 --}}
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

            {{-- 休憩中 → 休憩戻 --}}
            @if ($status === '休憩中')
                <form method="POST" action="{{ route('user.break.out') }}">
                    @csrf
                    <button type="submit" class="breakout-btn">
                        <span>休憩戻</span>
                    </button>
                </form>
            @endif

            {{-- 退勤済 → メッセージ --}}
            @if ($status === '退勤済')
                <p class="finished-message">お疲れ様でした。</p>
            @endif

        </div>
    </div>

@endsection