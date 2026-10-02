@extends('layouts.app')

@section('css')
    @vite('resources/css/user/attendance-report.css')
@endsection

@section('content')

    <h1>{{ $user->name }}さんの勤怠レポート（過去6ヶ月）</h1>

    {{-- 基本サマリー --}}
    <div class="summary">
        <h2>基本サマリー</h2>
        <ul>
            <li>総労働時間：{{ round($summary['total_work'] / 60, 1) }} 時間</li>
            <li>総残業時間：{{ round($summary['total_overtime'] / 60, 1) }} 時間</li>
            <li>平均労働時間：{{ round($summary['average_work'] / 60, 1) }} 時間</li>
            <li>出勤日数：{{ $summary['days'] }} 日</li>
        </ul>
    </div>

    {{-- 月次推移 --}}
    <div class="monthly">
        <h2>月次推移</h2>
        <table>
            <tr>
                <th>月</th>
                <th>労働時間</th>
                <th>残業時間</th>
                <th>遅刻回数</th>
                <th>早退回数</th>
                <th>長時間労働回数</th>
            </tr>
            @foreach($monthly as $month => $m)
                <tr>
                    <td>{{ $month }}</td>
                    <td>{{ round($m['work'] / 60, 1) }} h</td>
                    <td>{{ round($m['overtime'] / 60, 1) }} h</td>
                    <td>{{ $m['late_count'] }}</td>
                    <td>{{ $m['early_leave_count'] }}</td>
                    <td>{{ $m['long_work_count'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

@endsection