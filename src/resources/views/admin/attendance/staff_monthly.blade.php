@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff_monthly.css') }}">
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

    {{-- ▼ CSV 出力 --}}
    <div class="admin-csv-area">
        <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $currentMonth]) }}"
           class="admin-csv-btn">
            CSV出力
        </a>
    </div>

    {{-- ▼ 白い大枠 --}}
    <div class="admin-attendance-monthly-table-container">

        {{-- ▼ テーブルヘッダー --}}
        <div class="admin-attendance-monthly-table-header">
            <div class="admin-header-item admin-header-date">日付</div>
            <div class="admin-header-item admin-header-start">出勤</div>
            <div class="admin-header-item admin-header-end">退勤</div>
            <div class="admin-header-item admin-header-break">休憩</div>
            <div class="admin-header-item admin-header-total">実働</div>
            <div class="admin-header-item admin-header-detail">備考</div>
        </div>

        {{-- ▼ テーブル行 --}}
        @foreach ($attendances as $attendance)
            <div class="admin-attendance-monthly-row">
                <div class="admin-row-item admin-row-date">{{ $attendance->work_date }}</div>
                <div class="admin-row-item admin-row-start">{{ $attendance->clock_in ?? '' }}</div>
                <div class="admin-row-item admin-row-end">{{ $attendance->clock_out ?? '' }}</div>
                <div class="admin-row-item admin-row-break">{{ $attendance->break_time ?? '' }}</div>
                <div class="admin-row-item admin-row-total">{{ $attendance->working_time ?? '' }}</div>
                <div class="admin-row-item admin-row-detail">
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">
                        詳細
                    </a>
                </div>
            </div>
        @endforeach

    </div>

</div>

@endsection