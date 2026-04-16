@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/approve.css') }}">
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

@endphp

<div class="detail-container">
<div class="detail-header">
    <div class="bar"></div>
    <h1 class="title">勤怠詳細</h1>
</div>

<div class="detail-box">

    <div class="row">
        <span class="label">名前</span>
        <span class="text-value">{{ $request->user->name }}</span>
    </div>
    <div class="detail-line-1"></div>

    <div class="row">
        <span class="label">日付</span>
        <span class="text-value">{{ $request->attendance->work_date }}</span>
    </div>
    <div class="detail-line-2"></div>

    <div class="row">
        <span class="label">出勤・退勤</span>
        <span class="value">
            <span class="time-input">{{ $request->attendance->start_time_label }}</span>
            <span class="tilde">～</span>
            <span class="time-input">{{ $request->attendance->end_time_label }}</span>
        </span>
    </div>
    <div class="detail-line-3"></div>

    <div class="row">
        <span class="label">休憩1</span>
        <span class="value">
            <span class="time-input">
                {{ $break1? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '' }}
            </span>
            <span class="tilde">～</span>
            <span class="time-input">
                {{ $break1 && $break1->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '' }}
            </span>
        </span>
    </div>

    <div class="detail-line-4"></div>

    <div class="row">
        <span class="label">休憩2</span>
        <span class="value">
            <span class="time-input">
                {{ $break2? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '' }}
            </span>
            <span class="tilde">～</span>
            <span class="time-input">
                {{ $break2 && $break2->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '' }}
            </span>
        </span>
    </div>
    <div class="detail-line-5"></div>

    <div class="row">
        <span class="label">備考</span>
        <span class="text-value">{{ $request->reason }}</span>
    </div>
    

</div>

    @if ($request->status === 'pending')
        <form action="{{ route('admin.stamp_correction_request.update', ['attendance_correct_request_id' => $request->id]) }}" method="POST">
            @csrf
            <button type="submit" class="approve-btn">承認</button>
        </form>

    @elseif ($request->status === 'approved')
        <button class="approved-btn" disabled>承認済み</button>
    @endif
</div>

</div>

@endsection