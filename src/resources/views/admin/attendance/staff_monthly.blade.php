@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff_monthly.css') }}">
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

<div class="admin-attendance-monthly-container">

    {{-- ▼ タイトル（縦線＋◯◯さんの勤怠） --}}
    <div class="admin-attendance-monthly-title">
        <div class="admin-attendance-monthly-title-line"></div>
        <div class="admin-attendance-monthly-title-text">
            {{ $user->name }} さんの勤怠
        </div>
    </div>

    {{-- ▼ 月移動バー --}}
    <div class="admin-attendance-monthly-date-bar">

        {{-- ← 前月 --}}
        <div style="display:flex; align-items:center;">
            <img src="{{ asset('images/user/attendance/arrow-left.png') }}" class="admin-prev-icon">
            <a href="{{ route('admin.attendance.staff.monthly', ['id' => $user->id, 'month' => $prevMonth]) }}"
               class="admin-prev-text">
                前月
            </a>
        </div>

        {{-- カレンダー＋年月 --}}
        <div class="admin-attendance-monthly-date-center">
            <img src="{{ asset('images/user/attendance/calendar.png') }}" class="admin-calendar-icon">
            <span class="admin-current-month">{{ $currentMonth }}</span>
        </div>

        {{-- 翌月 → --}}
        <div style="display:flex; align-items:center;">
            <a href="{{ route('admin.attendance.staff.monthly', ['id' => $user->id, 'month' => $nextMonth]) }}"
               class="admin-next-text">
                翌月
            </a>
            <img src="{{ asset('images/user/attendance/arrow-right.png') }}" class="admin-next-icon">
        </div>

    </div>

    {{-- ▼ 白い大枠 --}}
    <div class="admin-attendance-monthly-table-container">

        {{-- ▼ テーブルヘッダー --}}
        <div class="admin-attendance-monthly-table-header">
            <div class="admin-header-item admin-header-date">日付</div>
            <div class="admin-header-item admin-header-start">出勤</div>
            <div class="admin-header-item admin-header-end">退勤</div>
            <div class="admin-header-item admin-header-break">休憩</div>
            <div class="admin-header-item admin-header-total">合計</div>
            <div class="admin-header-item admin-header-detail">備考</div>
        </div>

        {{-- ▼ テーブル行 --}}
        @foreach ($attendances as $attendance)
            <div class="admin-attendance-monthly-row">

                {{-- 日付：06/01(木) --}}
                @php
                    $wMap = [
                        'Sun' => '日',
                        'Mon' => '月',
                        'Tue' => '火',
                        'Wed' => '水',
                        'Thu' => '木',
                        'Fri' => '金',
                        'Sat' => '土',
                    ];

                    $date = \Carbon\Carbon::parse($attendance->work_date);
                    $w = $wMap[$date->format('D')];
                @endphp

                <div class="admin-row-item admin-row-date">
                    {{ $date->format('m/d') }}({{ $w }})
                </div>

                {{-- 出勤：09:00 --}}
                <div class="admin-row-item admin-row-start">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                </div>

                {{-- 退勤：18:00 --}}
                <div class="admin-row-item admin-row-end">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                </div>

                {{-- 休憩：1:00（分→H:i に変換） --}}
                <div class="admin-row-item admin-row-break">
                    {{ $attendance->break_time_label }}
                </div>

                {{-- 合計：8:00（分→H:i に変換） --}}
                <div class="admin-row-item admin-row-total">
                    {{ $attendance->total_time_label }}
                </div>

                <div class="admin-row-item admin-row-detail">
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">
                        詳細
                    </a>
                </div>

            </div>
        @endforeach

    </div>

    {{-- ▼ CSV 出力 --}}
    <div class="admin-csv-area">
        <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $currentMonth]) }}"
           class="admin-csv-btn">
            CSV出力
        </a>
    </div>
</div>

@endsection