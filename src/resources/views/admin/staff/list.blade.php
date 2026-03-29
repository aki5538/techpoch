@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
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

<div class="staff-list-container">

    {{-- タイトル --}}
    <div class="page-header">
        <div class="page-bar"></div>
        <h1 class="page-title">スタッフ一覧</h1>
    </div>
    {{-- スタッフ一覧テーブル --}}
    <div class="table-wrapper">
        {{-- テーブルヘッダー --}}
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
        </table>

        {{-- Figma の太い区切り線（必須） --}}
        <div class="table-divider"></div>

        {{-- テーブル本体 --}}
        <table class="staff-table">
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.staff.monthly', $user->id) }}" class="detail-link">
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