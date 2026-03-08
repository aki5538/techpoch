@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('header-menu')
<nav class="attendance-header-menu">
    <a href="{{ route('user.attendance') }}">勤怠</a>
    <a href="{{ route('user.attendance.list') }}">勤怠一覧</a>
    <a href="{{ route('stamp_correction_request.list') }}">申請</a>
    <a href="{{ route('logout') }}">ログアウト</a>
</nav>
@endsection

@section('content')

{{-- detail-container は背景だけ --}}
<div class="detail-container">
    <div class="detail-header">
        <div class="bar"></div>
        <div class="title">勤怠詳細</div>
    </div>
</div>

{{-- detail-box は container の外に出す（方法①の核心） --}}
<div class="detail-box
    {{ ($attendance->correctionRequest && $attendance->correctionRequest->status === 'pending')
        ? 'detail-box-pending'
        : ''
    }}">
    
    <div class="detail-name-label">名前</div>
    <div class="detail-name-value">{{ $user->name }}</div>
    <div class="detail-line-1"></div>

    <div class="detail-date-label">日付</div>
    <div class="detail-date-year">{{ $attendance->work_date->format('Y年') }}</div>
    <div class="detail-date-day">{{ $attendance->work_date->format('n月j日') }}</div>
    <div class="detail-line-2"></div>

    <div class="detail-worktime-label">出勤・退勤</div>
    
    <div class="detail-start-box"></div>
    <div class="detail-start-time">{{ $attendance->clock_in->format('H:i') }}</div>

    <div class="detail-tilde">～</div>

    <div class="detail-end-box"></div>
    <div class="detail-end-time">{{ $attendance->clock_out->format('H:i') }}</div>
    <div class="detail-line-3"></div>

    <div class="detail-break1-label">休憩</div>
    <div class="detail-break-box">
        @foreach ($attendance->breakTimes as $break)
            <div class="detail-break1-start-time">{{ $break->break_in }}</div>
            <div class="detail-break1-tilde">～</div>
            <div class="detail-break1-end-time">{{ $break->break_out }}</div>
        @endforeach
    </div>
    <div class="detail-line-4"></div>

    <div class="detail-note-label">備考</div>
    <div class="detail-note-text">{{ $attendance->note }}</div>
</div>

{{-- 修正ボタン --}}
@if(!$attendance->correctionRequest || $attendance->correctionRequest->status !== 'pending')
    <form action="{{ route('stamp_correction_request.store', $attendance->id) }}" method="POST">
        @csrf
        <button type="submit" class="detail-edit-button">
            <span class="detail-edit-button-text">修正</span>
        </button>
    </form>
@else
    <div class="detail-pending-message">*承認待ちのため修正はできません。</div>
@endif

@endsection