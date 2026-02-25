@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/attendance-list.css') }}">
@endsection

@section('content')

    {{-- ▼ 黒帯の右上メニュー --}}
    <nav class="attendance-header-menu">
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        <a href="{{ route('logout') }}">ログアウト</a>
    </nav>


    {{-- ▼ 日付バー（←画像 / カレンダー画像 / →画像） --}}
    <div class="attendance-list-date-bar">

        {{-- ←（画像） --}}
        <img src="{{ asset('images/user/attendance/arrow-left.png') }}" class="date-prev-icon">

        {{-- 前月 --}}
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="date-prev-text">
            前月
        </a>

        {{-- カレンダー画像 --}}
        <img src="{{ asset('images/user/attendance/calendar.png') }}" class="date-calendar-icon">

        {{-- 年月（例：2023/06） --}}
        <span class="date-current">
            {{ $currentMonthLabel }}
        </span>

        {{-- 翌月 --}}
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="date-next-text">
            翌月
        </a>

        {{-- →（画像） --}}
        <img src="{{ asset('images/user/attendance/arrow-left.png') }}" class="date-next-icon">

    </div>


    {{-- ▼ 勤怠一覧テーブル --}}
    <div class="attendance-list-table-container">

        {{-- テーブルヘッダー --}}
        <div class="attendance-list-table-header">
            <div class="attendance-list-header-item attendance-list-header-date">日付</div>
            <div class="attendance-list-header-item attendance-list-header-start">出勤</div>
            <div class="attendance-list-header-item attendance-list-header-end">退勤</div>
            <div class="attendance-list-header-item attendance-list-header-break">休憩</div>
            <div class="attendance-list-header-item attendance-list-header-total">合計</div>
            <div class="attendance-list-header-item attendance-list-header-detail">詳細</div>
        </div>

        {{-- テーブル行 --}}
        @foreach ($attendances as $attendance)
            <div class="attendance-list-row">
                <div class="attendance-list-row-item attendance-list-row-date">
                    {{ $attendance->date_label }}
                </div>
                <div class="attendance-list-row-item attendance-list-row-start">
                    {{ $attendance->start_time_label }}
                </div>
                <div class="attendance-list-row-item attendance-list-row-end">
                    {{ $attendance->end_time_label }}
                </div>
                <div class="attendance-list-row-item attendance-list-row-break">
                    {{ $attendance->break_time_label }}
                </div>
                <div class="attendance-list-row-item attendance-list-row-total">
                    {{ $attendance->total_time_label }}
                </div>
                <div class="attendance-list-row-item attendance-list-row-detail">
                    <a href="{{ route('user.attendance.detail', $attendance->id) }}">詳細</a>
                </div>
            </div>
        @endforeach

    </div>

@endsection