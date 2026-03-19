@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

{{-- ヘッダー（ユーザー側と同じ構成） --}}
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

<div class="attendance-list-container">

    {{-- ▼ タイトル（ユーザー側と同じ構成） --}}
    <div class="attendance-list-title">
        <div class="attendance-list-title-line"></div>
        <div class="attendance-list-title-text">
            {{ \Carbon\Carbon::parse($date)->format('Y年n月j日の勤怠') }}
        </div>
    </div>

    {{-- ここから先は今の date-bar / table をそのまま使ってOK --}}
    {{-- ▼ 日付バー --}}
    <div class="attendance-list-date-bar">
        <div class="date-left">
            <img src="{{ asset('images/admin/attendance/arrow-left.png') }}" class="date-prev-icon">
            <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}"
               class="date-prev-text">
                前日
            </a>
        </div>

        <div class="date-center">
            <img src="{{ asset('images/admin/attendance/calendar.png') }}" class="date-calendar-icon">
            <span class="date-current">
                {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
            </span>
        </div>

        <div class="date-right">
            <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}"
               class="date-next-text">
                翌日
            </a>
            <img src="{{ asset('images/admin/attendance/arrow-left.png') }}" class="date-next-icon">
        </div>
    </div>

    {{-- ▼ 勤怠一覧テーブル（ここは今のままでOK） --}}
    <div class="attendance-list-table-container">

        <div class="attendance-list-table-header">
            <div class="attendance-list-header-item attendance-list-header-name">名前</div>
            <div class="attendance-list-header-item attendance-list-header-start">出勤</div>
            <div class="attendance-list-header-item attendance-list-header-end">退勤</div>
            <div class="attendance-list-header-item attendance-list-header-break">休憩</div>
            <div class="attendance-list-header-item attendance-list-header-total">合計</div>
            <div class="attendance-list-header-item attendance-list-header-detail">詳細</div>
        </div>

        @foreach ($attendances as $attendance)
            <div class="attendance-list-row">
                <div class="attendance-list-row-item attendance-list-row-name">
                    {{ $attendance->user->name }}
                </div>

                <div class="attendance-list-row-item attendance-list-row-start">
                    {{ optional($attendance->clock_in)->format('H:i') }}
                </div>

                <div class="attendance-list-row-item attendance-list-row-end">
                    {{ optional($attendance->clock_out)->format('H:i') }}
                </div>

                <div class="attendance-list-row-item attendance-list-row-break">
                    {{ $attendance->break_time_label }}
                </div>

                <div class="attendance-list-row-item attendance-list-row-total">
                    {{ $attendance->total_time_label }}
                </div>

                <div class="attendance-list-row-item attendance-list-row-detail">
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection