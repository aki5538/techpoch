@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/stamp_correction_request/list.css') }}">
@endsection

@section('content')

    {{-- 黒帯の中の右上メニュー --}}
    <nav class="attendance-header-menu">
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        <a href="{{ route('logout') }}">ログアウト</a>
    </nav>

    {{-- 画面全体の背景（全幅） --}}
    <div class="page-bg">

        {{-- コンテンツ（1512px 中央寄せ） --}}
        <div class="page-container">

            <div class="page-title-wrapper">
                <div class="title-line"></div>
                <h1 class="page-title">申請一覧</h1>
            </div>

            {{-- タブ切り替え --}}
            <div class="tabs">
                <a href="#pending" class="tab active">承認待ち</a>
                <a href="#approved" class="tab">承認済み</a>
            </div>

            <div class="request-list-line"></div>

            {{-- 承認待ち --}}
            <div id="pending" class="tab-content active">
                <div class="request-table-wrapper">
                    <table class="request-table">
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
                    </table>

                    <div class="request-table-divider"></div>

                    <table class="request-table">
                        <tbody>
                            @foreach ($pending as $item)
                            <tr>
                                <td>承認待ち</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->attendance->work_date }}</td>
                                <td>{{ $item->reason }}</td>
                                <td>{{ $item->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('attendance.detail', ['id' => $item->attendance_id]) }}" class="detail-link">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


            {{-- 承認済み --}}
            <div id="approved" class="tab-content">
                <div class="request-table-wrapper">
                    <table class="request-table">
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
                    </table>

                    <div class="request-table-divider"></div>

                    <table class="request-table">
                        <tbody>
                            @foreach ($approved as $item)
                            <tr>
                                <td>承認済み</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->attendance->work_date }}</td>
                                <td>{{ $item->reason }}</td>
                                <td>{{ $item->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('attendance.detail', ['id' => $item->attendance_id]) }}" class="detail-link">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>