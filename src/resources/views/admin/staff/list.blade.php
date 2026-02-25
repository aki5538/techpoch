@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
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

<div class="staff-list-container">

    {{-- タイトル --}}
    <div class="page-header">
        <div class="page-bar"></div>
        <h1 class="page-title">スタッフ一覧</h1>
    </div>
    {{-- スタッフ一覧テーブル --}}
    <div class="table-wrapper">
        {{-- ヘッダー3項目（Figma の top/left そのまま） --}}
        <div class="th-name">名前</div>
        <div class="th-email">メールアドレス</div>
        <div class="th-monthly">月次勤怠</div>

        {{-- 区切り線 Line1 --}}
        <div class="table-divider"></div>

        {{-- テーブル本体（行だけ table で OK） --}}
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ url('/admin/attendance/staff/' . $user->id) }}" class="detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection