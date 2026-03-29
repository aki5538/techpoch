@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('header-menu')
    <nav class="attendance-header-menu">
        <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請一覧</a>

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

@php
    $break1 = $attendance->breakTimes[0] ?? null;
    $break2 = $attendance->breakTimes[1] ?? null;

    $pending = $attendance->correctionRequest && $attendance->correctionRequest->status === 'pending';
@endphp

<div class="detail-container">

    <div class="detail-header">
        <div class="bar"></div>
        <div class="title">勤怠詳細</div>
    </div>

    {{-- ★ 管理者は直接修正するので form はここ --}}
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf

        {{-- ★ 白枠（ユーザー側と同じ構造） --}}
        <div class="detail-box {{ $pending ? 'detail-box-pending' : '' }}">

            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value text-value">{{ $attendance->user->name }}</div>
            </div>
            <div class="detail-line-1"></div>

            {{-- 日付 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value text-value">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                </div>
                <div class="value text-value">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                </div>
            </div>
            <div class="detail-line-2"></div>

            {{-- 出勤・退勤 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>

                <div class="value">
                    <input type="text"
                        name="clock_in"
                        class="time-input"
                        value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}"
                        @if($pending) disabled @endif>

                    <span class="tilde">～</span>

                    <input type="text"
                        name="clock_out"
                        class="time-input"
                        value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}"
                        @if($pending) disabled @endif>
                </div>
            </div>

            @error('clock_in') <div class="error-message">{{ $message }}</div> @enderror
            @error('clock_out') <div class="error-message">{{ $message }}</div> @enderror

            <div class="detail-line-3"></div>

            {{-- 休憩1 --}}
            <div class="row">
                <div class="label">休憩</div>
                <div class="value">

                    <input type="text"
                           name="break_start_1"
                           class="time-input"
                           value="{{ old('break_start_1', optional($break1)->break_in ? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '') }}"
                           @if($pending) disabled @endif>

                    <span class="tilde">～</span>

                    <input type="text"
                           name="break_end_1"
                           class="time-input"
                           value="{{ old('break_end_1', optional($break1)->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '') }}"
                           @if($pending) disabled @endif>

                </div>
            </div>

            @error('break_start_1') <div class="error-message">{{ $message }}</div> @enderror
            @error('break_end_1')   <div class="error-message">{{ $message }}</div> @enderror

            <div class="detail-line-4"></div>

            {{-- 休憩2 --}}
            <div class="row">
                <div class="label">休憩2</div>
                <div class="value">

                    <input type="text"
                           name="break_start_2"
                           class="time-input"
                           value="{{ old('break_start_2', optional($break2)->break_in ? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '') }}"
                           @if($pending) disabled @endif>

                    <span class="tilde">～</span>

                    <input type="text"
                           name="break_end_2"
                           class="time-input"
                           value="{{ old('break_end_2', optional($break2)->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '') }}"
                           @if($pending) disabled @endif>

                </div>
            </div>

            @error('break_start_2') <div class="error-message">{{ $message }}</div> @enderror
            @error('break_end_2')   <div class="error-message">{{ $message }}</div> @enderror

            <div class="detail-line-5"></div>

            {{-- 備考 --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="value note-value">

                    <textarea name="note"
                              class="detail-note-textarea"
                              @if($pending) disabled @endif>{{ old('note', $attendance->note) }}</textarea>

                </div>
            </div>

            @error('note') <div class="error-message">{{ $message }}</div> @enderror

        </div>{{-- /detail-box --}}

        {{-- 承認待ち --}}
        @if($pending)
            <div class="detail-pending-message">※ 承認待ちのため修正はできません。</div>
        @endif

        {{-- 修正ボタン（白枠の外） --}}
        @if(!$pending)
            <button type="submit" class="detail-edit-button">
                <span class="detail-edit-button-text">修正</span>
            </button>
        @endif

    </form>

</div>
@endsection