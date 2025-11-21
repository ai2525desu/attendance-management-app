@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('content')
<div class="user-attendance-list__content">
    <h2 class="user-attendance-list__title">
        勤怠一覧
    </h2>
    <div class="user-attendance-list__pagination">
        <a class="pagination__previous-month" href="{{ route('user.attendance.list', ['year' => $previous->year, 'month' => $previous->month]) }}">
            <img class="pagination__previous-month--left-arrow" src="{{ asset('storage/arrow.svg') }}" alt="←">
            <span class="pagination__previous-month--title">前月</span>
        </a>
        <a class="pagination__current-month" href="{{ route('user.attendance.list') }}">
            <img class="pagination__current-month--calendar-icon" src="{{ asset('storage/calendar.svg') }}" alt="カレンダーアイコン">
            <span class="pagination__current-month--title">{{ $targetDate->format('Y/m') }}</span>
        </a>
        <a class="pagination__next-month" href="{{ route('user.attendance.list', ['year' => $next->year, 'month' => $next->month]) }}">
            <span class="pagination__next-month--title">翌月</span>
            <img class="pagination__next-month--right-arrow" src="{{ asset('storage/arrow.svg') }}" alt="→">
        </a>
    </div>
    <div class="user-attendance-list__monthly-attendance">
        <table>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach ($daysInMonth as $day)
            <tr>
                <td>
                    {{ $day['date']->isoFormat('M/D(ddd)') }}
                </td>
                <td>
                    {{ $day['attendance'] ? $day['attendance']->clock_in->format('H:i') : '' }}
                </td>
                <td>
                    {{ $day['attendance'] ? $day['attendance']->clock_out->format('H:i') : '' }}
                </td>
                <td>
                    {{ $day['total_break_format']}}
                </td>
                <td>
                    {{ $day['total_working_time_format']}}
                </td>
                <td>
                    @if ($day['attendance'])
                    <a class="monthly-attendance__screen-transition" href="{{ route('user.attendance.detail', ['id' => $day['attendance']->id]) }}">
                        詳細
                    </a>
                    @else
                    <span class="monthly-attendance__screen-transition">詳細</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection