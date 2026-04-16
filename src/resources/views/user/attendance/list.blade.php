@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance/attendance-list.css') }}">
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

<div class="attendance-list-container">

    <div class="attendance-list-title">
        <div class="attendance-list-title-line"></div>
        <div class="attendance-list-title-text">勤怠一覧</div>
    </div>

    <div class="attendance-list-date-bar">

        <div class="date-left">
            <img src="{{ asset('images/user/attendance/arrow-left.png') }}" class="date-prev-icon">
            <a href="{{ route('user.attendance.list', ['month' => $prevMonth]) }}" class="date-prev-text">
                前月
            </a>
        </div>

        <div class="date-center">
            <img src="{{ asset('images/user/attendance/calendar.png') }}" class="date-calendar-icon">
            <span class="date-current">{{ $currentMonthLabel }}</span>
        </div>

        <div class="date-right">
            <a href="{{ route('user.attendance.list', ['month' => $nextMonth]) }}" class="date-next-text">
                翌月
            </a>
            <img src="{{ asset('images/user/attendance/arrow-left.png') }}" class="date-next-icon">
        </div>
    </div>

    <div class="attendance-list-table-container">

        <div class="attendance-list-table-header">
            <div class="attendance-list-header-item attendance-list-header-date">日付</div>
            <div class="attendance-list-header-item attendance-list-header-start">出勤</div>
            <div class="attendance-list-header-item attendance-list-header-end">退勤</div>
            <div class="attendance-list-header-item attendance-list-header-break">休憩</div>
            <div class="attendance-list-header-item attendance-list-header-total">合計</div>
            <div class="attendance-list-header-item attendance-list-header-detail">詳細</div>
        </div>

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
</div>
@endsection