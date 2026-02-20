@extends('layouts.user')

@section('content')
<div class="detail-container">

    {{-- タイトル --}}
    <div class="detail-header">
        <div class="bar"></div>
        <div class="title">勤怠詳細</div>
    </div>

    {{-- メイン白枠 --}}
    <div class="detail-box">
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

        <div class="detail-break-box">
            <div class="detail-break1-label">休憩</div>
            <div class="detail-break1-start-box"></div>
            <div class="detail-break1-start-time">
                {{ $attendance->break_start_1 }}
            </div>
            <div class="detail-break1-tilde">～</div>
            <div class="detail-break1-end-box"></div>
            <div class="detail-break1-end-time">
                {{ $attendance->break_end_1 }}
            </div>
            <div class="detail-line-4"></div>
        </div>

        <div class="detail-note-label">備考</div>
        <div class="detail-note-box"></div>
        <div class="detail-note-text">
            {{ $attendance->note }}
        </div>
    </div>

    <form action="{{ route('user.attendance.edit', $attendance->id) }}" method="GET">
        <button type="submit" class="detail-edit-button">
            <span class="detail-edit-button-text">修正</span>
        </button>
    </form>
</div>
@endsection