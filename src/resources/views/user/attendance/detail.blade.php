@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
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

@php
    $break1 = $attendance->breakTimes[0] ?? null;
    $break2 = $attendance->breakTimes[1] ?? null;

    // 最新の修正申請だけ取得
    $latestRequest = $attendance->correctionRequests()->latest()->first();
@endphp

<div class="detail-container">
    <div class="detail-header">
        <div class="bar"></div>
        <div class="title">勤怠詳細</div>
    </div>

    {{-- ★ 白ボックス開始 --}}
    <div class="detail-box
        {{ ($latestRequest && $latestRequest->status === 'pending')
            ? 'detail-box-pending'
            : ''
        }}">

        {{-- 名前 --}}
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ $user->name }}</div>
        </div>
        <div class="detail-line-1"></div>

        {{-- 日付 --}}
        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                {{ $attendance->work_date->format('Y年 n月j日') }}
            </div>
        </div>
        <div class="detail-line-2"></div>

        {{-- 出勤・退勤 --}}
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="value">
                <div class="time-box">
                    {{ $attendance->clock_in->format('H:i') }}
                </div>
                <span class="tilde">～</span>
                <div class="time-box">
                    {{ $attendance->clock_out->format('H:i') }}
                </div>
            </div>
        </div>
        <div class="detail-line-3"></div>

        {{-- 休憩1 --}}
        <div class="row">
            <div class="label">休憩</div>
            <div class="value">
                <div class="time-box">{{ optional($break1)->break_in ? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '' }}</div>
                <span class="tilde">～</span>
                <div class="time-box">{{ optional($break1)->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '' }}</div>
            </div>
        </div>
        <div class="detail-line-4"></div>

        {{-- 休憩2 --}}
        <div class="row">
            <div class="label">休憩2</div>
            <div class="value">
                <div class="time-box">{{ optional($break2)->break_in ? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '' }}</div>
                <span class="tilde">～</span>
                <div class="time-box">{{ optional($break2)->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '' }}</div>
            </div>
        </div>
        <div class="detail-line-5"></div>

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>
            <div class="value note-value">
                <div class="detail-note-box"></div>
                <div class="detail-note-text">{{ $attendance->note }}</div>
            </div>
        </div>

    </div>

    @if($latestRequest && $latestRequest->status === 'pending')
        <div class="detail-pending-message">*承認待ちのため修正はできません。</div>
    @else
        <form action="{{ route('stamp_correction_request.store', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <button type="submit" class="detail-edit-button">
                <span class="detail-edit-button-text">修正</span>
            </button>
        </form>
    @endif

</div>
@endsection