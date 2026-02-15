{{-- resources/views/user/clock.blade.php --}}
@extends('layouts.user')

@section('content')
<div class="clock-container">

    {{-- ステータス表示 --}}
    <h2>{{ $status }}</h2>

    {{-- 日付表示 --}}
    <p>{{ $today }}</p>

    {{-- 時刻表示 --}}
    <p>{{ $now }}</p>

    {{-- ボタン出し分け --}}
    <div class="buttons">
        @if ($status === '勤務外')
            <form method="POST" action="{{ route('user.clock.in') }}">
                @csrf
                <button type="submit">出勤</button>
            </form>
        @endif

        @if ($status === '出勤中')
            <form method="POST" action="{{ route('user.break.in') }}">
                @csrf
                <button type="submit">休憩入</button>
            </form>

            <form method="POST" action="{{ route('user.clock.out') }}">
                @csrf
                <button type="submit">退勤</button>
            </form>
        @endif

        @if ($status === '休憩中')
            <form method="POST" action="{{ route('user.break.out') }}">
                @csrf
                <button type="submit">休憩戻</button>
            </form>
        @endif

        @if ($status === '退勤済')
            <p>お疲れ様でした。</p>
        @endif
    </div>

</div>
@endsection