@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/detail.css') }}">
@endsection

{{-- ヘッダー（ユーザー側と同じ構成） --}}
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
    $attendance = $request->attendance;
    $break1 = $attendance->breakTimes[0] ?? null;
    $break2 = $attendance->breakTimes[1] ?? null;
@endphp

<div class="attendance-list-container">

    <div class="detail-container">

        <div class="detail-header">
            <div class="bar"></div>
            <div class="title">勤怠</div>
        </div>

        <form action="{{ route('admin.stamp_correction_request.update', ['attendance_correct_request_id' => $request->id]) }}"
        method="POST">
        @csrf

        <div class="detail-box">

            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value text-value">{{ $request->user->name }}</div>
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
                    <div class="time-box">
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                    </div>

                    <span class="tilde">～</span>

                    <div class="time-box">
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                    </div>
                </div>
            </div>
            <div class="detail-line-3"></div>

            {{-- 休憩1 --}}
            <div class="row">
                <div class="label">休憩</div>
                <div class="value">
                    <div class="time-box">
                        {{ optional($break1)->break_in ? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '' }}
                    </div>

                    <span class="tilde">～</span>

                    <div class="time-box">
                        {{ optional($break1)->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '' }}
                    </div>
                </div>
            </div>
            <div class="detail-line-4"></div>

            {{-- 休憩2 --}}
            <div class="row">
                <div class="label">休憩2</div>
                <div class="value">
                    <div class="time-box">
                        {{ optional($break2)->break_in ? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '' }}
                    </div>

                    <span class="tilde">～</span>

                    <div class="time-box">
                        {{ optional($break2)->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '' }}
                    </div>
                </div>
            </div>
            <div class="detail-line-5"></div>

            {{-- 備考（申請理由） --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="value note-value">
                    <div class="detail-note-text">
                        {{ $request->reason }}
                    </div>
                </div>
            </div>

        </div>

        {{-- 承認ボタン --}}
        <form action="{{ route('admin.stamp_correction_request.update', ['attendance_correct_request_id' => $request->id]) }}"
            method="POST">
            @csrf
            <button type="submit" class="detail-edit-button">
                <span class="detail-edit-button-text">承認</span>
            </button>
        </form>

    </div>
</div>

@endsection

