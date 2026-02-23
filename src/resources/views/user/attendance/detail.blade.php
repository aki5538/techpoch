@extends('layouts.user')

@section('content')

{{-- 黒帯の中の右上メニュー --}}
<nav class="attendance-header-menu">
    <a href="{{ route('attendance.index') }}">勤怠</a>
    <a href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a href="{{ route('stamp_correction_request.list') }}">申請</a>
    <a href="{{ route('logout') }}">ログアウト</a>
</nav>

<div class="detail-container">

    {{-- タイトル --}}
    <div class="detail-header">
        <div class="bar"></div>
        <div class="title">勤怠詳細</div>
    </div>

    {{-- メイン白枠 --}}
    <div class="detail-box 
        {{ ($attendance->correctionRequest && $attendance->correctionRequest->status === 'pending') 
            ? 'detail-box-pending' 
            : '' 
        }}">

        <div class="detail-name-label">名前</div>
        <div class="detail-name-value">{{ $user->name }}</div>
        <div class="detail-line-1"></div>

        <div class="detail-date-label">日付</div>
        <div class="detail-date-year">
            {{ $attendance->work_date->format('Y年') }}
        </div>
        <div class="detail-date-day">
            {{ $attendance->work_date->format('n月j日') }}
        </div>
        <div class="detail-line-2"></div>

        <div class="detail-worktime-box"></div>
        <div class="detail-worktime-label">出勤・退勤</div>
        <div class="detail-start-box"></div>
        <div class="detail-start-time">{{ $attendance->clock_in }}</div>
        <div class="detail-tilde">～</div>
        <div class="detail-end-box"></div>
        <div class="detail-end-time">{{ $attendance->clock_out }}</div>
        <div class="detail-line-3"></div>

        {{-- 休憩 --}}
        <div class="detail-break-box">
            <div class="detail-break1-label">休憩</div>

            @foreach ($attendance->breakTimes as $break)
                <div class="detail-break1-start-box"></div>
                <div class="detail-break1-start-time">
                    {{ $break->break_in }}
                </div>

                <div class="detail-break1-tilde">～</div>

                <div class="detail-break1-end-box"></div>
                <div class="detail-break1-end-time">
                    {{ $break->break_out }}
                </div>

                <div class="detail-line-4"></div>
            @endforeach
        </div>

        <div class="detail-note-label">備考</div>
        <div class="detail-note-box"></div>
        <div class="detail-note-text">
            {{ $attendance->note }}
        </div>
    </div>

    {{-- 承認待ちメッセージ or 修正ボタン --}}
    @if($attendance->correctionRequest && $attendance->correctionRequest->status === 'pending')
        <div class="detail-pending-message">
            *承認待ちのため修正はできません。
        </div>
    @else
        <form action="{{ route('stamp_correction_request.store', $attendance->id) }}" method="POST">
            @csrf
            <button type="submit" class="detail-edit-button">
                <span class="detail-edit-button-text">修正</span>
            </button>
        </form>
    @endif
</div>
@endsection