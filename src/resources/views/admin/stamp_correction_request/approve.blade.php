@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/approve.css') }}">
@endsection

@section('content')

<div class="admin-nav">
    <a href="/admin/attendance/list">勤怠一覧</a>
    <a href="/admin/staff/list">スタッフ一覧</a>
    <a href="/admin/stamp_correction_request/list">申請一覧</a>

    <form method="POST" action="/admin/logout" class="admin-logout-form">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</div>

<div class="correction-detail-container">

    <h2>修正申請詳細（承認）</h2>

    <div class="detail-box">

        <div class="row">
            <span class="label">名前</span>
            <span class="value">{{ $request->user->name }}</span>
        </div>

        <div class="row">
            <span class="label">日付</span>
            <span class="value">{{ $request->attendance->work_date }}</span>
        </div>

        <div class="row">
            <span class="label">出勤・退勤</span>
            <span class="value">
                {{ $request->attendance->start_time_label }} ～ {{ $request->attendance->end_time_label }}
            </span>
        </div>

        <div class="row">
            <span class="label">休憩</span>
            <span class="value">
                @foreach ($request->attendance->breakTimes as $break)
                    {{ \Carbon\Carbon::parse($break->break_in)->format('H:i') }}
                    ～
                    {{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}
                    <br>
                @endforeach
            </span>
        </div>

        <div class="row">
            <span class="label">備考</span>
            <span class="value">{{ $request->reason }}</span>
        </div>

    </div>

    {{-- 承認ボタン --}}
    <form action="{{ route('stamp_correction_request.approve', $request->id) }}" method="GET">
        @csrf
        <button type="submit" class="approve-btn">承認</button>
    </form>

</div>

@endsection