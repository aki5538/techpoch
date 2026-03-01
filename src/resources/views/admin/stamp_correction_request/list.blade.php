@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="page-title">修正申請一覧</h2>

    {{-- 承認待ち --}}
    <h3 class="section-title">承認待ち</h3>
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
            @forelse ($pending as $item)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->attendance->work_date }}</td>
                    <td>{{ $item->reason }}</td>
                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ url('/stamp_correction_request/approve/' . $item->id) }}"
                           class="detail-link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">承認待ちはありません</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- 承認済み --}}
    <h3 class="section-title">承認済み</h3>
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
            @forelse ($approved as $item)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->attendance->work_date }}</td>
                    <td>{{ $item->reason }}</td>
                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ url('/stamp_correction_request/approve/' . $item->id) }}"
                           class="detail-link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">承認済みはありません</td></tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection