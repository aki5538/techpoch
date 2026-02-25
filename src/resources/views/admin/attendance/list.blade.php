@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')

{{-- 管理者ナビ（各画面ごとに実装） --}}
<div class="admin-nav">
    <a href="/admin/attendance/list">勤怠一覧</a>
    <a href="/admin/staff/list">スタッフ一覧</a>
    <a href="/admin/stamp_correction_request/list">申請一覧</a>

    <form method="POST" action="/admin/logout" class="admin-logout-form">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</div>

<div class="attendance-list-container">

    {{-- タイトルブロック（棒 + タイトル） --}}
    <div class="page-title-block">
        <div class="title-line"></div>

        <h1 class="page-title">
            {{ \Carbon\Carbon::parse($date)->format('Y年n月j日の勤怠') }}
        </h1>
    </div>

    {{-- 日付（前日 / 今日 / 翌日） --}}
    <div class="date-bar">

        {{-- ←（画像） --}}
        <img src="{{ asset('images/admin/attendance/arrow-left.png') }}" class="date-prev-icon">

        {{-- 前日 --}}
        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}"
        class="date-prev-text">
            前日
        </a>

        {{-- カレンダー画像 --}}
        <img src="{{ asset('images/admin/attendance/calendar.png') }}" class="date-calendar-icon">

        {{-- 日付（例：2023/06/01） --}}
        <span class="date-current">
            {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
        </span>

        {{-- 翌日 --}}
        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}"
        class="date-next-text">
            翌日
        </a>

        {{-- →（画像：← を反転） --}}
        <img src="{{ asset('images/admin/attendance/arrow-left.png') }}" class="date-next-icon">

    </div>

    {{-- 勤怠一覧テーブル --}}
    <table class="attendance-list-table">
        <thead>
            <tr>
                <th class="attendance-header-name">名前</th>
                <th class="attendance-header-start">出勤</th>
                <th class="attendance-header-end">退勤</th>
                <th class="attendance-header-break">休憩</th>
                <th class="attendance-header-total">合計</th>
                <th class="attendance-header-detail">詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($attendances as $index => $attendance)
                <tr class="attendance-row" style="top: {{ 60 + ($index * 33) }}px;">
                    <td class="attendance-row-item row-name">
                        {{ $attendance->user->name }}</td>
                    <td class="attendance-row-item row-start">
                        {{ $attendance->clock_in ?? '' }}</td>
                    <td class="attendance-row-item row-end">
                        {{ $attendance->clock_out ?? '' }}</td>
                    <td class="attendance-row-item row-break">
                        {{ $attendance->break_total ?? '' }}</td>
                    <td class="attendance-row-item row-total">
                        {{ $attendance->work_total ?? '' }}</td>
                    <td class="attendance-row-item row-detail">
                        <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection