@extends('layouts.app') {{-- あなたの管理者レイアウトに合わせて変更 --}}

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff_monthly.css') }}">
@endsection

@section('content')

<div class="monthly-wrapper">

    {{-- タイトル --}}
    <h2>{{ $user->name }} さんの勤怠</h2>

    {{-- 月移動 --}}
    <div class="month-nav">
        <a href="{{ route('admin.attendance.staff.monthly', ['id' => $user->id, 'month' => $prevMonth]) }}">◀ 前月</a>
        <span>{{ $currentMonth }}</span>
        <a href="{{ route('admin.attendance.staff.monthly', ['id' => $user->id, 'month' => $nextMonth]) }}">翌月 ▶</a>
    </div>

    {{-- CSV 出力 --}}
    <div class="csv-area">
        <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $currentMonth]) }}" class="csv-btn">
            CSV出力
        </a>
    </div>

    {{-- 勤怠一覧テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>実働</th>
                <th>備考</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date }}</td>
                    <td>{{ $attendance->start_time ?? '' }}</td>
                    <td>{{ $attendance->end_time ?? '' }}</td>
                    <td>{{ $attendance->break_time ?? '' }}</td>
                    <td>{{ $attendance->working_time ?? '' }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection