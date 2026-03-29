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
                value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">

            <span class="tilde">～</span>

            <input type="text" name="clock_out" class="time-input"
                value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
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
                value="{{ optional($break1)->break_in ? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '' }}">

            <span class="tilde">～</span>

            <input type="text" name="break1_out" class="time-input"
                value="{{ optional($break1)->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '' }}">
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
                value="{{ optional($break2)->break_in ? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '' }}">

            <span class="tilde">～</span>

            <input type="text" name="break2_out" class="time-input"
                value="{{ optional($break2)->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '' }}">
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
            <textarea id="note-input" name="note" class="detail-note-textarea" required>
                {{ old('note', $attendance->note) }}
            </textarea>
        </div>
    </div>

    @error('note')
        <div class="error-message">{{ $message }}</div>
    @enderror

</div>

{{-- 修正フォーム --}}
<form id="correction-form"
    action="{{ route('stamp_correction_request.store', ['id' => $attendance->id]) }}"
    method="POST">
    @csrf

    <input type="hidden" name="clock_in" id="clock_in_hidden">
    <input type="hidden" name="clock_out" id="clock_out_hidden">

    <input type="hidden" name="break1_in" id="break1_in_hidden">
    <input type="hidden" name="break1_out" id="break1_out_hidden">

    <input type="hidden" name="break2_in" id="break2_in_hidden">
    <input type="hidden" name="break2_out" id="break2_out_hidden">

    <input type="hidden" name="note" id="note-hidden">

    <button type="button" id="submit-btn" class="detail-edit-button">
        <span class="detail-edit-button-text">修正</span>
    </button>
</form>

<script>
document.getElementById('submit-btn').addEventListener('click', function() {

    document.getElementById('clock_in_hidden').value =
        document.querySelector('input[name="clock_in"]').value;

    document.getElementById('clock_out_hidden').value =
        document.querySelector('input[name="clock_out"]').value;

    document.getElementById('break1_in_hidden').value =
        document.querySelector('input[name="break1_in"]').value;

    document.getElementById('break1_out_hidden').value =
        document.querySelector('input[name="break1_out"]').value;

    document.getElementById('break2_in_hidden').value =
        document.querySelector('input[name="break2_in"]').value;

    document.getElementById('break2_out_hidden').value =
        document.querySelector('input[name="break2_out"]').value;

    document.getElementById('note-hidden').value =
        document.getElementById('note-input').value;

    document.getElementById('correction-form').submit();
});
</script>

</div>
@endsection