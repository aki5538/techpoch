@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/list.css') }}">
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

<div class="container">

    <h2 class="page-title">修正申請一覧</h2>

    {{-- 承認待ち --}}
    <h3 class="section-title {{ request()->get('tab', 'pending') === 'pending' ? 'active' : 'inactive' }}">
        承認待ち
    </h3>

    <table class="table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pending as $index => $item)
                <tr class="pg12-row pg12-row-{{ $index + 1 }}">
                    <td class="pg12-td pg12-td-status">承認待ち</td>
                    <td class="pg12-td pg12-td-name">{{ $item->user->name }}</td>
                    <td class="pg12-td pg12-td-date">{{ $item->attendance->work_date }}</td>
                    <td class="pg12-td pg12-td-reason">{{ $item->reason }}</td>
                    <td class="pg12-td pg12-td-created">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    <td class="pg12-td pg12-td-detail">
                        <a href="{{ url('/stamp_correction_request/approve/' . $item->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 承認済み --}}
    <h3 class="section-title {{ request()->get('tab') === 'approved' ? 'active' : 'inactive' }}">
        承認済み
    </h3>

    <table class="table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($approved as $index => $item)
                <tr class="pg12-row pg12-row-{{ $index + 1 }}">
                    <td class="pg12-td pg12-td-status">承認済み</td>
                    <td class="pg12-td pg12-td-name">{{ $item->user->name }}</td>
                    <td class="pg12-td pg12-td-date">{{ $item->attendance->work_date }}</td>
                    <td class="pg12-td pg12-td-reason">{{ $item->reason }}</td>
                    <td class="pg12-td pg12-td-created">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    <td class="pg12-td pg12-td-detail">
                        <a href="{{ url('/stamp_correction_request/approve/' . $item->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection