@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/list.css') }}">
@endsection

{{-- ヘッダー（ユーザー側と同じ構成） --}}
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

<div class="correction-list-container">

    {{-- タイトル --}}
    <div class="page-header">
        <div class="page-bar"></div>
        <h1 class="page-title">申請一覧</h1>
    </div>

    {{-- タブ（承認待ち / 承認済み） --}}
    <div class="pg12-tabs">
        <a href="?tab=pending"
           class="pg12-tab {{ request('tab','pending') === 'pending' ? 'active' : 'inactive' }}">
            承認待ち
        </a>

        <a href="?tab=approved"
           class="pg12-tab {{ request('tab') === 'approved' ? 'active' : 'inactive' }}">
            承認済み
        </a>
    </div>

    <div class="pg12-tab-line"></div>

    {{-- 承認待ちタブ --}}
    @if (request('tab','pending') === 'pending')
    <div class="table-wrapper">
        <div class="table-box">

            <table class="correction-table">
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
                    @foreach ($pending as $item)
                        <tr>
                            <td>承認待ち</td>
                            <td>{{ $item->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->attendance->work_date)->format('Y/m/d') }}</td>
                            <td>{{ $item->reason }}</td>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.stamp_correction_request.approve', ['attendance_correct_request_id' => $item->id]) }}"
                                    class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    @endif

    {{-- 承認済みタブ --}}
    @if (request('tab') === 'approved')
    <div class="table-wrapper">
        <div class="table-box">

            <table class="correction-table">
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
                    @foreach ($approved as $item)
                        <tr>
                            <td>承認済み</td>
                            <td>{{ $item->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->attendance->work_date)->format('Y/m/d') }}</td>
                            <td>{{ $item->reason }}</td>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.stamp_correction_request.approve', ['attendance_correct_request_id' => $item->id]) }}"
                                    class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    @endif

</div>

@endsection