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

    $latestRequest = $attendance->correctionRequests()->latest()->first();
@endphp

<div class="detail-container">
    <div class="detail-header">
        <div class="bar"></div>
        <div class="title">勤怠詳細</div>
    </div>

    {{-- 入力欄は全部 form の中に入れる --}}
    <form id="correction-form"
        action="{{ route('stamp_correction_request.store', ['attendanceId' => $attendance->id]) }}"
        method="POST">
        @csrf

        <div class="detail-box
        {{ ($latestRequest && $latestRequest->status === 'pending')
            ? 'detail-box-pending'
            : ''
        }}">

            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value text-value">{{ $user->name }}</div>
            </div>
            <div class="detail-line-1"></div>

            {{-- 日付 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value text-value">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}
                </div>
            </div>
            <div class="detail-line-2"></div>

            {{-- 出勤・退勤 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value">
                    <input type="text" name="clock_in" class="time-input"
                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">

                    <span class="tilde">～</span>

                    <input type="text" name="clock_out" class="time-input"
                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                </div>
            </div>

            @error('clock_in')
                <div class="error-message">{{ $message }}</div>
            @enderror
            @error('clock_out')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <div class="detail-line-3"></div>

            {{-- 休憩1 --}}
            <div class="row">
                <div class="label">休憩</div>
                <div class="value">
                    <input type="text" name="break1_in" class="time-input"
                        value="{{ old('break1_in', optional($break1)->break_in ? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '') }}">

                    <span class="tilde">～</span>

                    <input type="text" name="break1_out" class="time-input"
                        value="{{ old('break1_out', optional($break1)->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '') }}">
                </div>
            </div>

            @error('break1_in')
                <div class="error-message">{{ $message }}</div>
            @enderror
            @error('break1_out')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <div class="detail-line-4"></div>

            {{-- 休憩2 --}}
            <div class="row">
                <div class="label">休憩2</div>
                <div class="value">
                    <input type="text" name="break2_in" class="time-input"
                        value="{{ old('break2_in', optional($break2)->break_in ? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '') }}">

                    <span class="tilde">～</span>

                    <input type="text" name="break2_out" class="time-input"
                        value="{{ old('break2_out', optional($break2)->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '') }}">
                </div>
            </div>

            @error('break2_in')
                <div class="error-message">{{ $message }}</div>
            @enderror
            @error('break2_out')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <div class="detail-line-5"></div>

            {{-- 備考 --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="value note-value">
                    <textarea name="note" class="detail-note-textarea">{{ old('note', $attendance->note) }}</textarea>
                </div>
            </div>

            @error('note')
                <div class="error-message">{{ $message }}</div>
            @enderror

        </div> {{-- detail-box end --}}

        {{-- 修正ボタン（form の中でも外でも OK） --}}
        <button type="submit" class="detail-edit-button">
            <span class="detail-edit-button-text">修正</span>
        </button>

    </form>

</div>

@endsection