@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/stamp_correction_request/list.css') }}">
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

    <div class="page-bg">
        <div class="page-container">

            <div class="page-title-wrapper">
                <div class="title-line"></div>
                <h1 class="page-title">申請一覧</h1>
            </div>

            {{-- タブ --}}
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

                        <tbody>
                            @foreach ($pending as $item)
                            <tr>
                                <td>承認待ち</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->attendance->work_date->format('Y/m/d') }}</td>
                                <td>{{ $item->reason }}</td>
                                <td>{{ $item->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('user.attendance.detail', ['id' => $item->attendance_id,
                                        'status' => 'pending']) }}"
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

                        <tbody>
                            @foreach ($approved as $item)
                            <tr>
                                <td>承認済み</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->attendance->work_date->format('Y/m/d') }}</td>
                                <td>{{ $item->reason }}</td>
                                <td>{{ $item->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('user.attendance.detail', ['id' => $item->attendance_id,
                                        'status' => 'approved']) }}"
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

        </div>
    </div>
    <script>
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            // タブの active 切り替え
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // コンテンツの active 切り替え
            const target = this.getAttribute('href').replace('#', '');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(target).classList.add('active');
        });
    });
    </script>
@endsection