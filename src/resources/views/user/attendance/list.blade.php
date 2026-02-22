@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/attendance-list.css') }}">
@endsection

@section('content')

    {{-- ▼ 黒帯の中の右上メニュー --}}
    <nav class="attendance-header-menu">
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        <a href="{{ route('logout') }}">ログアウト</a>
    </nav>

    <div class="attendance-list-table-container">

        {{-- ヘッダー --}}
        <div class="attendance-list-table-header">
            <div class="attendance-list-header-item attendance-list-header-date">日付</div>
            <div class="attendance-list-header-item attendance-list-header-start">出勤</div>
            <div class="attendance-list-header-item attendance-list-header-end">退勤</div>
            <div class="attendance-list-header-item attendance-list-header-break">休憩</div>
            <div class="attendance-list-header-item attendance-list-header-total">合計</div>
            <div class="attendance-list-header-item attendance-list-header-detail">詳細</div>
        </div>

        {{-- 行 --}}
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