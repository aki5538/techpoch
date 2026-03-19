@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

{{-- ヘッダー --}}
@section('header-menu')
    <nav class="attendance-header-menu">
        <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
        <a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a>

        <a href="#"
           onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
            ログアウト
        </a>

        <form id="admin-logout-form"
              action="{{ url('/admin/logout') }}"
              method="POST"
              style="display:none;">
            @csrf
        </form>
    </nav>
@endsection

@section('content')

{{-- タイトル --}}
<div class="detail-header">
    <div class="bar"></div>
    <div class="title">勤怠詳細</div>
</div>

{{-- 白い大枠 --}}
<div class="detail-wrapper"></div>

{{-- 名前 --}}
<div class="detail-name-label">名前</div>
<div class="detail-name-value">{{ $attendance->user->name }}</div>
<div class="detail-line-1"></div>

{{-- 日付 --}}
<div class="detail-date-label">日付</div>
<div class="detail-date-year">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</div>
<div class="detail-date-day">{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</div>
<div class="detail-line-2"></div>

{{-- 出勤・退勤 --}}
<div class="detail-worktime-label">出勤・退勤</div>

<div class="detail-start-box"></div>
<div class="detail-start-time">
    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
</div>

<div class="detail-tilde">〜</div>

<div class="detail-end-box"></div>
<div class="detail-end-time">
    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
</div>

<div class="detail-line-3"></div>

{{-- 休憩1 --}}
<div class="detail-break-box"></div>

<div class="detail-break1-label">休憩</div>

<div class="detail-break1-start-box"></div>
<div class="detail-break1-start-time">
    {{ $attendance->breakTimes->get(0)?->break_in
        ? \Carbon\Carbon::parse($attendance->breakTimes->get(0)->break_in)->format('H:i')
        : '' }}
</div>

<div class="detail-break1-tilde">〜</div>

<div class="detail-break1-end-box"></div>
<div class="detail-break1-end-time">
    {{ $attendance->breakTimes->get(0)?->break_out
        ? \Carbon\Carbon::parse($attendance->breakTimes->get(0)->break_out)->format('H:i')
        : '' }}
</div>

<div class="detail-line-4"></div>

{{-- 休憩2 --}}
<div class="detail-break2-label">休憩2</div>

<div class="detail-break2-start-box"></div>
<div class="detail-break2-start-time">
    {{ $attendance->breakTimes->get(1)?->break_in
        ? \Carbon\Carbon::parse($attendance->breakTimes->get(1)->break_in)->format('H:i')
        : '' }}
</div>

<div class="detail-break2-tilde">〜</div>

<div class="detail-break2-end-box"></div>
<div class="detail-break2-end-time">
    {{ $attendance->breakTimes->get(1)?->break_out
        ? \Carbon\Carbon::parse($attendance->breakTimes->get(1)->break_out)->format('H:i')
        : '' }}
</div>

<div class="detail-line-5"></div>

{{-- 備考 --}}
<div class="detail-note-label">備考</div>
<div class="detail-note-box"></div>
<div class="detail-note-text">
    {{ $attendance->note }}
</div>

{{-- 承認待ち --}}
@if ($attendance->correctionRequest && $attendance->correctionRequest->status === 'pending')
    <div class="detail-pending-message">
        ※ この勤怠は修正申請が承認待ちのため、修正できません。
    </div>
@endif

{{-- 修正フォーム --}}
<div class="attendance-detail-container">

    <h2 class="page-title">
        {{ $attendance->user->name }} さんの勤怠修正
    </h2>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf

        {{-- 出勤時間 --}}
        <div class="form-group">
            <label for="clock_in">出勤時間</label>
            <input type="time"
                   name="clock_in"
                   id="clock_in"
                   value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
            @error('clock_in')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 退勤時間 --}}
        <div class="form-group">
            <label for="clock_out">退勤時間</label>
            <input type="time"
                   name="clock_out"
                   id="clock_out"
                   value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
            @error('clock_out')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 休憩時間（固定2枠） --}}
        <div class="break-time-wrapper">
            <label>休憩時間</label>

            {{-- 休憩1 --}}
            <div class="break-row">
                <input type="time"
                       name="break_start_1"
                       value="{{ old('break_start_1', $attendance->breakTimes->get(0)?->break_in ? \Carbon\Carbon::parse($attendance->breakTimes->get(0)->break_in)->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time"
                       name="break_end_1"
                       value="{{ old('break_end_1', $attendance->breakTimes->get(0)?->break_out ? \Carbon\Carbon::parse($attendance->breakTimes->get(0)->break_out)->format('H:i') : '') }}">
            </div>

            {{-- 休憩2 --}}
            <div class="break-row">
                <input type="time"
                       name="break_start_2"
                       value="{{ old('break_start_2', $attendance->breakTimes->get(1)?->break_in ? \Carbon\Carbon::parse($attendance->breakTimes->get(1)->break_in)->format('H:i') : '') }}">
                <span>〜</span>
                <input type="time"
                       name="break_end_2"
                       value="{{ old('break_end_2', $attendance->breakTimes->get(1)?->break_out ? \Carbon\Carbon::parse($attendance->breakTimes->get(1)->break_out)->format('H:i') : '') }}">
            </div>

            @error('break_start_1') <p class="error">{{ $message }}</p> @enderror
            @error('break_end_1')   <p class="error">{{ $message }}</p> @enderror
            @error('break_start_2') <p class="error">{{ $message }}</p> @enderror
            @error('break_end_2')   <p class="error">{{ $message }}</p> @enderror
        </div>

        {{-- 備考 --}}
        <div class="form-group">
            <label for="note">備考</label>
            <textarea name="note" id="note" rows="4">{{ old('note', $attendance->note) }}</textarea>
            @error('note')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 修正ボタン --}}
        @if (!($attendance->correctionRequest && $attendance->correctionRequest->status === 'pending'))
            <button type="submit" class="btn-submit">修正する</button>
        @endif

    </form>
</div>

@endsection